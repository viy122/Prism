#!/usr/bin/env python3
"""
PRISM Market Scoping Microservice v2.0

Merged from:
  prism-matcher/app.py  — semantic matching via sentence-transformers
  microservice/app.py   — three-mode detection, TF-IDF fallback

Endpoints:
  GET  /health       — Service status and capability flags
  POST /match        — Mode-aware filtering and scoring
  POST /advantageous — Budget-aware decision support with semantic value detection
1
Port: 5001
"""

# ── Standard library ──────────────────────────────────────────────────────────
import hashlib
import json as _json
import os
import re

# ── Flask ─────────────────────────────────────────────────────────────────────
from flask import Flask, request, jsonify

# ── Environment (.env in same directory) ─────────────────────────────────────
try:
    from dotenv import load_dotenv
    load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))
except ImportError:
    pass

app = Flask(__name__)

# ── Match cache (process-lifetime, keyed by content hash) ────────────────────
_match_cache = {}
_CACHE_LIMIT  = 200


def _to_float(value, default: float = 0.0) -> float:
    """Parse numbers that may arrive as None or formatted strings ('1,234.00', '₱500')."""
    if value is None:
        return default
    if isinstance(value, (int, float)):
        return float(value)
    cleaned = re.sub(r'[^0-9.\-]', '', str(value))
    try:
        return float(cleaned) if cleaned else default
    except ValueError:
        return default


def _make_cache_key(item: str, specs: list, results: list) -> str:
    r_hash = hashlib.md5(_json.dumps(
        [r.get('name', r.get('title', '')) + str(r.get('price', '')) for r in results]
    ).encode()).hexdigest()[:8]
    payload = f"{item.lower()}|{'|'.join(sorted(s.lower() for s in specs))}|{r_hash}"
    return hashlib.md5(payload.encode()).hexdigest()


# ════════════════════════════════════════════════════════════════════════════════
# 1. CONSTANTS
# ════════════════════════════════════════════════════════════════════════════════

# Words that alone indicate a broad/category-level search
BROAD_TERMS = {
    'supplies', 'consumables', 'materials', 'equipment', 'items', 'goods',
    'misc', 'miscellaneous', 'janitorial', 'stationery', 'various',
    'assorted', 'general', 'tools', 'accessories', 'parts', 'hardware',
    'paraphernalia', 'provisions', 'others', 'other',
    # Filipino procurement terms
    'kagamitan', 'gamit', 'materyales',
}

# Words that are only broad when the majority of the query is also broad
CONTEXT_BROAD = {'office', 'school', 'laboratory', 'lab', 'classroom', 'medical'}

STOP_WORDS = {
    'for', 'the', 'and', 'with', 'of', 'per', 'mga', 'ang', 'na', 'ng',
    'is', 'an', 'a', 'or', 'to', 'in', 'on', 'at', 'by', 'as', 'its',
    'various', 'different', 'etc',
}

# Keywords that signal added value in a product listing (used in /advantageous)
VALUE_KEYWORDS = [
    # Bundle / package signals
    'bundle', 'bundled', 'kit', 'set', 'pack', 'combo', 'package',
    'complete', 'full set', 'all-in-one', 'multifunction', 'multi-function',
    # Inclusion signals
    'free', 'bonus', 'extra', 'includes', 'included', 'comes with',
    'plus', 'accessories', 'attachment', 'add-on',
    # Common bundled items in Philippine office/school procurement
    'toner', 'cartridge', 'ink', 'dryer', 'stand', 'rack', 'case',
    'bag', 'cover', 'cable', 'adapter', 'charger', 'mouse', 'keyboard',
    # Quality / assurance signals
    'warranty', 'guarantee', 'service', 'support', 'after-sales',
    'premium', 'pro', 'professional', 'heavy duty', 'high capacity',
    'official', 'genuine', 'authentic', 'certified', 'original',
]

# How far over budget a result may go and still qualify as advantageous
# (0.30 = 30%). Must match the rules described in the /advantageous docstring.
OVER_BUDGET_TOLERANCE = 0.30

# Natural language concepts precomputed for semantic value detection
_VALUE_CONCEPTS = [
    "includes additional items or accessories",
    "bundled with complementary products",
    "complete package with free extras",
    "more features and inclusions for the price",
    "better value with added bonuses",
]


# ════════════════════════════════════════════════════════════════════════════════
# 1b. SIGNATURE DETECTION  (OpenCV, optional — /detect-signature + /blur-region)
# ════════════════════════════════════════════════════════════════════════════════

_CV_AVAILABLE = False
try:
    import base64 as _base64

    import cv2 as _cv2
    import numpy as _np
    _CV_AVAILABLE = True
except ImportError:
    pass

# Detection tuning — page-relative bounds for a signature-like ink blob
SIG_MAX_EDGE          = 1600    # downscale long edge before analysis
SIG_MIN_AREA_RATIO    = 0.001   # >= 0.1% of the page
SIG_MAX_AREA_RATIO    = 0.08    # <= 8% of the page
SIG_MIN_ASPECT        = 1.2     # signatures are wider than tall
SIG_MAX_ASPECT        = 10.0
SIG_MIN_FILL          = 0.02    # ink pixels / box area — strokes, not solid blocks
SIG_MAX_FILL          = 0.45
SIG_BOX_PAD           = 0.15    # padding applied around each box before blurring
SIG_LOWER_HALF_BONUS  = 0.25    # signatures usually sit in the lower half of a form


def _decode_image(data: bytes):
    """bytes → BGR ndarray, downscaled to SIG_MAX_EDGE; returns (img, scale)."""
    img = _cv2.imdecode(_np.frombuffer(data, _np.uint8), _cv2.IMREAD_COLOR)
    if img is None:
        return None, 1.0
    h, w = img.shape[:2]
    scale = 1.0
    long_edge = max(h, w)
    if long_edge > SIG_MAX_EDGE:
        scale = SIG_MAX_EDGE / long_edge
        img = _cv2.resize(img, (int(w * scale), int(h * scale)), interpolation=_cv2.INTER_AREA)
    return img, scale


def _find_signature_boxes(img) -> list:
    """
    Heuristic ink detection: adaptive threshold → dilate to merge strokes →
    connected components scored as signature-like. Returns [{x,y,w,h,score}]
    in the (possibly downscaled) image's coordinates, best first.
    """
    h, w = img.shape[:2]
    page_area = h * w

    gray = _cv2.cvtColor(img, _cv2.COLOR_BGR2GRAY)
    binary = _cv2.adaptiveThreshold(
        gray, 255, _cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        _cv2.THRESH_BINARY_INV, 25, 15,
    )

    # Suppress long straight ruling lines (table borders, underlines) so they
    # do not glue printed text together with genuine pen strokes.
    horiz = _cv2.morphologyEx(binary, _cv2.MORPH_OPEN, _cv2.getStructuringElement(_cv2.MORPH_RECT, (int(w * 0.25) | 1, 1)))
    vert  = _cv2.morphologyEx(binary, _cv2.MORPH_OPEN, _cv2.getStructuringElement(_cv2.MORPH_RECT, (1, int(h * 0.25) | 1)))
    ink = _cv2.subtract(binary, _cv2.bitwise_or(horiz, vert))

    # Merge nearby strokes into blobs (signatures are loopy and disconnected)
    merged = _cv2.dilate(ink, _cv2.getStructuringElement(_cv2.MORPH_ELLIPSE, (13, 7)), iterations=2)

    contours, _ = _cv2.findContours(merged, _cv2.RETR_EXTERNAL, _cv2.CHAIN_APPROX_SIMPLE)
    boxes = []
    for contour in contours:
        x, y, bw, bh = _cv2.boundingRect(contour)
        if bh == 0 or bw == 0:
            continue
        area_ratio = (bw * bh) / page_area
        aspect = bw / bh
        if not (SIG_MIN_AREA_RATIO <= area_ratio <= SIG_MAX_AREA_RATIO):
            continue
        if not (SIG_MIN_ASPECT <= aspect <= SIG_MAX_ASPECT):
            continue

        roi = ink[y:y + bh, x:x + bw]
        fill = float(_np.count_nonzero(roi)) / (bw * bh)
        if not (SIG_MIN_FILL <= fill <= SIG_MAX_FILL):
            continue

        # Irregularity: signatures have far more perimeter than a printed word block
        perimeter = _cv2.arcLength(contour, True)
        hull_area = max(_cv2.contourArea(_cv2.convexHull(contour)), 1.0)
        irregularity = (perimeter ** 2) / (4 * _np.pi * hull_area)
        if irregularity < 2.0:
            continue

        score = min(irregularity / 10.0, 1.0) * 0.6 + (1.0 - abs(fill - 0.12) / 0.45) * 0.4
        if (y + bh / 2) > h * 0.45:
            score += SIG_LOWER_HALF_BONUS
        boxes.append({'x': x, 'y': y, 'w': bw, 'h': bh, 'score': round(float(score), 4)})

    boxes.sort(key=lambda b: b['score'], reverse=True)
    return boxes[:6]


def _blur_boxes(img, boxes: list):
    """Blur each padded box in-place on a copy of img; returns the copy."""
    out = img.copy()
    h, w = out.shape[:2]
    for box in boxes:
        pad_w = int(box['w'] * SIG_BOX_PAD)
        pad_h = int(box['h'] * SIG_BOX_PAD)
        x1 = max(int(box['x']) - pad_w, 0)
        y1 = max(int(box['y']) - pad_h, 0)
        x2 = min(int(box['x'] + box['w']) + pad_w, w)
        y2 = min(int(box['y'] + box['h']) + pad_h, h)
        if x2 <= x1 or y2 <= y1:
            continue
        roi = out[y1:y2, x1:x2]
        kernel = max((min(roi.shape[0], roi.shape[1]) // 2) | 1, 31)
        out[y1:y2, x1:x2] = _cv2.GaussianBlur(roi, (kernel, kernel), 0)
    return out


def _encode_jpeg_b64(img) -> str:
    ok, buf = _cv2.imencode('.jpg', img, [int(_cv2.IMWRITE_JPEG_QUALITY), 88])
    return _base64.b64encode(buf.tobytes()).decode('ascii') if ok else ''


def _read_request_image():
    """Pull image bytes from multipart 'image' or JSON 'image_b64'; None if absent."""
    if 'image' in request.files:
        return request.files['image'].read()
    data = request.get_json(silent=True) or {}
    if data.get('image_b64'):
        try:
            return _base64.b64decode(data['image_b64'])
        except Exception:
            return None
    return None


# ════════════════════════════════════════════════════════════════════════════════
# 2. MODEL LOADING  (sentence-transformers, loaded once at startup)
# ════════════════════════════════════════════════════════════════════════════════

_ST_AVAILABLE             = False
_ST_MODEL                 = None
_ST_UTIL                  = None
_VALUE_CONCEPT_EMBEDDINGS = None


def _load_sentence_transformers() -> bool:
    global _ST_AVAILABLE, _ST_MODEL, _ST_UTIL, _VALUE_CONCEPT_EMBEDDINGS
    try:
        from sentence_transformers import SentenceTransformer, util as _util
        _ST_MODEL                 = SentenceTransformer('all-MiniLM-L6-v2')
        _ST_UTIL                  = _util
        _VALUE_CONCEPT_EMBEDDINGS = _ST_MODEL.encode(_VALUE_CONCEPTS, convert_to_tensor=True)
        _ST_AVAILABLE             = True
        return True
    except ImportError:
        return False
    except Exception as e:
        print(f'  [!!] sentence-transformers failed: {e}')
        return False


# ════════════════════════════════════════════════════════════════════════════════
# 3. MODE DETECTION
# ════════════════════════════════════════════════════════════════════════════════

def detect_search_mode(item: str, specs: list) -> str:
    """
    Decide which filtering strategy to use for this search.

    'broad'    — category-level query (office supplies, consumables, lab equipment)
                 Behavior: skip filtering, return ALL results ranked by SerpAPI position
    'specific' — product name with specs (ballpen black 0.5mm)
                 Behavior: strict per-spec semantic / keyword filtering
    'general'  — product name without specs (laptop i7 16gb)
                 Behavior: loose semantic / TF-IDF ranking by item name similarity
    """
    raw   = re.split(r'[\s,/&()+]+', item.lower())
    words = [w for w in raw if w and w not in STOP_WORDS and len(w) >= 2]

    if not words:
        return 'broad'

    broad_count    = sum(1 for w in words if w in BROAD_TERMS)
    ctx_count      = sum(1 for w in words if w in CONTEXT_BROAD)
    specific_count = len(words) - broad_count - ctx_count

    # Every meaningful word is a broad/context term (e.g. "laboratory equipment")
    if specific_count == 0:
        return 'broad'

    # Majority (>=60%) are broad/context terms (e.g. "school supplies and materials")
    if (broad_count + ctx_count) / len(words) >= 0.60:
        return 'broad'

    return 'specific' if specs else 'general'


# ════════════════════════════════════════════════════════════════════════════════
# 4. MATCHING STRATEGIES
# ════════════════════════════════════════════════════════════════════════════════

def _product_text(r: dict) -> str:
    """Combine title and description into a single string for vectorization."""
    title = r.get('title', r.get('name', '')) or ''
    desc  = r.get('description', r.get('snippet', '')) or ''
    return f'{title} {desc}'.strip()


# ── 4a. Semantic spec match — Mode 1 primary ─────────────────────────────────

def _semantic_match(specs: list, results: list, threshold: float = 0.40) -> list:
    """
    Uses sentence_transformers: encodes each spec and each product title separately.
    A product passes when at least 80% of its per-spec cosine scores exceed the
    threshold (with 1-4 specs this means all of them must pass).
    Returns matched results sorted by average score descending.
    """
    if not _ST_AVAILABLE or not specs or not results:
        return []

    spec_embs  = _ST_MODEL.encode(specs, convert_to_tensor=True)
    titles     = [r.get('title', r.get('name', '')) for r in results]
    title_embs = _ST_MODEL.encode(titles, convert_to_tensor=True)

    matched = []
    for r, t_emb in zip(results, title_embs):
        per_spec_scores = [float(_ST_UTIL.cos_sim(s_emb, t_emb)) for s_emb in spec_embs]
        passing   = sum(1 for s in per_spec_scores if s > threshold)
        pass_rate = passing / len(per_spec_scores)
        if pass_rate >= 0.80:
            avg = sum(per_spec_scores) / len(per_spec_scores)
            matched.append({**r, 'match_score': round(avg, 4)})

    matched.sort(key=lambda x: x['match_score'], reverse=True)
    return matched


# ── 4b. Keyword spec match — Mode 1 fallback ─────────────────────────────────

def _keyword_match(specs: list, results: list, threshold: float = 0.35) -> list:
    """
    Calculates the fraction of spec keywords found in each product's text.
    Used when sentence_transformers is unavailable.
    """
    if not specs or not results:
        return []

    scored = []
    for r in results:
        combined = _product_text(r).lower()
        hits     = sum(1 for s in specs if s.lower() in combined)
        score    = round(hits / len(specs), 4)
        if score >= threshold:
            scored.append({**r, 'match_score': score})

    scored.sort(key=lambda x: x['match_score'], reverse=True)
    return scored


# ── 4c. Semantic rank — Mode 2 primary ───────────────────────────────────────

def _semantic_rank(item: str, results: list, threshold: float = 0.25) -> list:
    """
    Encodes the item name and compares it against each product's full text.
    Results below threshold are excluded. Used for general (no-spec) searches.
    """
    if not _ST_AVAILABLE or not item or not results:
        return []

    item_emb  = _ST_MODEL.encode(item, convert_to_tensor=True)
    texts     = [_product_text(r) for r in results]
    text_embs = _ST_MODEL.encode(texts, convert_to_tensor=True)

    ranked = []
    for r, t_emb in zip(results, text_embs):
        score = float(_ST_UTIL.cos_sim(item_emb, t_emb))
        if score >= threshold:
            ranked.append({**r, 'match_score': round(score, 4)})

    ranked.sort(key=lambda x: x['match_score'], reverse=True)
    return ranked


# ── 4d. TF-IDF rank — Mode 2 fallback ────────────────────────────────────────

def _tfidf_rank(query_text: str, results: list, threshold: float = 0.05) -> list:
    """
    scikit-learn TF-IDF cosine similarity against the query text.
    Used when sentence_transformers is unavailable.
    """
    if not results:
        return results
    try:
        from sklearn.feature_extraction.text import TfidfVectorizer
        from sklearn.metrics.pairwise import cosine_similarity

        corpus = [query_text] + [_product_text(r) for r in results]
        vec    = TfidfVectorizer(ngram_range=(1, 2), stop_words='english', min_df=1)
        mat    = vec.fit_transform(corpus)
        scores = cosine_similarity(mat[0:1], mat[1:]).flatten()

        ranked = []
        for r, score in zip(results, scores):
            if score >= threshold:
                ranked.append({**r, 'match_score': round(float(score), 4)})

        ranked.sort(key=lambda x: x['match_score'], reverse=True)
        return ranked if ranked else results
    except Exception:
        return results


# ── 4e. Broad rank — Mode 3 ──────────────────────────────────────────────────

def _broad_rank(results: list) -> list:
    """
    No filtering applied. Assigns declining scores by SerpAPI position
    (Google already sorted results by relevance).
    """
    n = max(len(results), 1)
    return [{**r, 'match_score': round(1.0 - (i / n), 4)} for i, r in enumerate(results)]


# ════════════════════════════════════════════════════════════════════════════════
# 5. ROUTES
# ════════════════════════════════════════════════════════════════════════════════

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status':              'ok',
        'version':             '2.1',
        'modes':               ['specific', 'general', 'broad'],
        'semantic_available':  _ST_AVAILABLE,
        'semantic_fallback':   'tfidf',
        'signature_detection': _CV_AVAILABLE,
    })


@app.route('/detect-signature', methods=['POST'])
def detect_signature():
    """
    Detect signature-like ink regions in a photo of a signed document and
    return a copy with those regions blurred.

    Input:  multipart field "image" (jpeg/png) OR JSON {"image_b64": "..."}
    Output: {
        "detected":         bool,
        "confidence":       float (best box score),
        "boxes":            [{"x","y","w","h","score"}]  (analysis-image coords),
        "blurred_image_b64": "<jpeg>"  (only when detected),
        "method":           "opencv"
    }
    When nothing signature-like is found, detected=false and no blurred image is
    returned — the caller decides whether to fall back to manual confirmation.
    """
    if not _CV_AVAILABLE:
        return jsonify({'error': 'signature detection unavailable — pip install opencv-python-headless'}), 503

    raw = _read_request_image()
    if not raw:
        return jsonify({'error': 'no image provided (multipart "image" or JSON "image_b64")'}), 400

    img, _scale = _decode_image(raw)
    if img is None:
        return jsonify({'error': 'could not decode image'}), 422

    boxes = _find_signature_boxes(img)
    if not boxes:
        return jsonify({'detected': False, 'confidence': 0.0, 'boxes': [], 'method': 'opencv'})

    return jsonify({
        'detected':          True,
        'confidence':        boxes[0]['score'],
        'boxes':             boxes,
        'blurred_image_b64': _encode_jpeg_b64(_blur_boxes(img, boxes)),
        'method':            'opencv',
    })


@app.route('/blur-region', methods=['POST'])
def blur_region():
    """
    Blur caller-specified regions (manual fallback when auto-detection misses).

    Input:  {"image_b64": "...", "boxes": [{"x","y","w","h"}]}
            Box coords are fractions of image size when <= 1.0, else pixels.
    Output: {"blurred_image_b64": "<jpeg>"}
    """
    if not _CV_AVAILABLE:
        return jsonify({'error': 'signature blurring unavailable — pip install opencv-python-headless'}), 503

    raw = _read_request_image()
    if not raw:
        return jsonify({'error': 'no image provided'}), 400

    data = request.get_json(silent=True) or {}
    boxes = data.get('boxes', [])
    if not boxes:
        return jsonify({'error': 'no boxes provided'}), 400

    img, _scale = _decode_image(raw)
    if img is None:
        return jsonify({'error': 'could not decode image'}), 422

    h, w = img.shape[:2]
    pixel_boxes = []
    for box in boxes:
        try:
            x, y, bw, bh = float(box['x']), float(box['y']), float(box['w']), float(box['h'])
        except (KeyError, TypeError, ValueError):
            continue
        if max(x, y, bw, bh) <= 1.0:   # fractional coords
            x, y, bw, bh = x * w, y * h, bw * w, bh * h
        pixel_boxes.append({'x': x, 'y': y, 'w': bw, 'h': bh})

    if not pixel_boxes:
        return jsonify({'error': 'no valid boxes provided'}), 400

    return jsonify({'blurred_image_b64': _encode_jpeg_b64(_blur_boxes(img, pixel_boxes))})


@app.route('/match', methods=['POST'])
def match():
    """
    Detect search mode then apply the appropriate filtering strategy.

    Input:
        {
            "item":    "ballpen black 0.5mm",
            "specs":   ["black", "ballpoint", "0.5mm"],
            "results": [ ...SerpAPI shopping results... ]
        }

    Output:
        {
            "mode":    "specific" | "general" | "broad",
            "matched": [ ...results with match_score field added... ],
            "count":   N
        }

    Priority per mode:
        specific → sentence_transformers > keyword fallback
        general  → sentence_transformers > TF-IDF fallback
        broad    → position-based scores, no filtering
    """
    data    = request.get_json(silent=True) or {}
    item    = str(data.get('item', '') or data.get('query', '')).strip()
    specs   = [str(s).strip() for s in data.get('specs', []) if str(s).strip()]
    results = data.get('results', [])

    if not results:
        return jsonify({'mode': 'broad', 'matched': [], 'count': 0})

    ck = _make_cache_key(item, specs, results)
    if ck in _match_cache:
        return jsonify(_match_cache[ck])

    mode = detect_search_mode(item, specs)

    # ── MODE 3: Broad ────────────────────────────────────────────────────────
    if mode == 'broad':
        matched = _broad_rank(results)

    # ── MODE 1: Specific (product + specs) ───────────────────────────────────
    elif mode == 'specific':
        matched = _semantic_match(specs, results)          # sentence_transformers

        if not matched:
            matched = _keyword_match(specs, results)       # keyword fallback

        if not matched:
            matched = _broad_rank(results)                 # nothing filtered — return all

    # ── MODE 2: General (product name, no specs) ──────────────────────────────
    else:
        matched = _semantic_rank(item, results)            # sentence_transformers
        if not matched:
            matched = _tfidf_rank(item, results)           # TF-IDF fallback
        if not matched or any('match_score' not in r for r in matched):
            matched = _broad_rank(results)

    result = {'mode': mode, 'matched': matched, 'count': len(matched)}
    if len(_match_cache) >= _CACHE_LIMIT:
        _match_cache.pop(next(iter(_match_cache)))   # evict oldest (insertion order)
    _match_cache[ck] = result
    return jsonify(result)


@app.route('/advantageous', methods=['POST'])
def advantageous():
    """
    Mark each result as advantageous based on budget and value signals.

    Rules (tolerance set by OVER_BUDGET_TOLERANCE):
      price <= budget                  → always advantageous ("Within budget")
      budget < price <= +tolerance     → advantageous only if value signals detected
                                          (keyword signals OR semantic signals via
                                          sentence_transformers)
      price > budget * (1 + tolerance) → not advantageous

    Input:  { "budget": 500.00, "results": [...] }
    Output: same results, each gains is_advantageous (bool) and reason (str)
    """
    data    = request.get_json(silent=True) or {}
    budget  = _to_float(data.get('budget'))
    results = data.get('results', [])

    if not results:
        return jsonify([])
    if budget <= 0:
        return jsonify(results)

    upper      = budget * (1 + OVER_BUDGET_TOLERANCE)
    output     = []
    borderline = []   # over-budget-within-tolerance entries, resolved after the loop

    for r in results:
        price = _to_float(r.get('price'))
        entry = {**r, 'is_advantageous': False, 'reason': ''}

        if price <= 0:
            pass                       # no usable price — leave unflagged

        elif price <= budget:
            entry['is_advantageous'] = True
            entry['reason']          = 'Within budget'

        elif price <= upper:
            combined = _product_text(r).lower()
            borderline.append({
                'idx':      len(output),
                'text':     combined,
                'top_kw':   [kw for kw in VALUE_KEYWORDS if kw in combined][:4],
                'over_amt': price - budget,
                'over_pct': (price - budget) / budget * 100,
            })

        output.append(entry)

    # Semantic value signals — one batched encode for all borderline entries
    sem_hit = {}
    if _ST_AVAILABLE and _VALUE_CONCEPT_EMBEDDINGS is not None and borderline:
        embs = _ST_MODEL.encode([b['text'] for b in borderline], convert_to_tensor=True)
        sims = _ST_UTIL.cos_sim(embs, _VALUE_CONCEPT_EMBEDDINGS)
        for b, row in zip(borderline, sims):
            sem_hit[b['idx']] = float(row.max()) > 0.35

    for b in borderline:
        reasons = []
        if b['top_kw']:
            reasons.append(f"includes value-added items: {', '.join(b['top_kw'])}")
        elif sem_hit.get(b['idx']):
            reasons.append('description suggests additional inclusions beyond the base item')

        if reasons:
            entry = output[b['idx']]
            entry['is_advantageous'] = True
            entry['reason']          = (
                f"Only ₱{b['over_amt']:,.2f} ({b['over_pct']:.1f}%) over budget — "
                + '; '.join(reasons)
            )

    return jsonify(output)


# ════════════════════════════════════════════════════════════════════════════════
# 6. CROSS-DOCUMENT ITEM MATCHING
# ════════════════════════════════════════════════════════════════════════════════

def _normalize_item_name(name: str) -> str:
    """Lowercase, strip punctuation noise, collapse whitespace."""
    s = re.sub(r'[^\w\s./-]', ' ', str(name or '').lower())
    return re.sub(r'\s+', ' ', s).strip()


def _token_overlap(a: str, b: str) -> float:
    """
    Containment-biased token overlap, 0.0-1.0.

    Deliberately asymmetric-friendly: a PPMP line often says just "monitor"
    while the PR spells out "Monitor, 24-inch LED Full HD Display". Dividing by
    the SHORTER token set means a short upstream name fully contained in a long
    downstream one scores 1.0 instead of being penalised for the extra detail.
    """
    ta = {t for t in _normalize_item_name(a).split() if len(t) > 1}
    tb = {t for t in _normalize_item_name(b).split() if len(t) > 1}
    if not ta or not tb:
        return 0.0
    return len(ta & tb) / min(len(ta), len(tb))


def _match_items_semantic(left_names: list, right_names: list):
    """Cosine similarity matrix via sentence-transformers, or None if unavailable."""
    if not _ST_AVAILABLE or not left_names or not right_names:
        return None
    try:
        le = _ST_MODEL.encode(left_names,  convert_to_tensor=True)
        re_ = _ST_MODEL.encode(right_names, convert_to_tensor=True)
        return _ST_UTIL.cos_sim(le, re_)          # shape: (len(left), len(right))
    except Exception as e:
        print(f'  [!!] /match-items semantic pass failed: {e}')
        return None


@app.route('/match-items', methods=['POST'])
def match_items():
    """
    Pair every item in `left` with its best counterpart in `right`.

    Used for cross-document validation: left = items read out of an uploaded
    document (a PR, quotation, AOC or PO), right = the items it is supposed to
    correspond to (the approved PPMP, the parent PR, and so on).

    Input:
        {
          "left":  [{"name": "Monitor, 24-inch LED", "quantity": 5, "unit": "unit"}, ...],
          "right": [{"name": "monitor", "quantity": 5, "unit": "unit"}, ...]
        }

    Output:
        {
          "strategy": "semantic" | "token",
          "matches": [
            {"left_index": 0, "right_index": 3, "score": 0.93, "matched": true},
            ...
          ],
          "unmatched_right": [2, 5]
        }

    Scores are 0.0-1.0. Every left item gets exactly one entry, in order; a left
    item with no acceptable counterpart comes back with right_index = null and
    matched = false. Right items are consumed at most once, so two near-identical
    PR lines cannot both claim the same PPMP line.
    """
    data  = request.get_json(silent=True) or {}
    left  = data.get('left', [])  or []
    right = data.get('right', []) or []
    try:
        threshold = float(data.get('threshold', 0.55))
    except (TypeError, ValueError):
        threshold = 0.55

    if not left:
        return jsonify({'strategy': 'none', 'matches': [], 'unmatched_right': list(range(len(right)))})
    if not right:
        return jsonify({
            'strategy': 'none',
            'matches': [{'left_index': i, 'right_index': None, 'score': 0.0, 'matched': False}
                        for i in range(len(left))],
            'unmatched_right': [],
        })

    left_names  = [str(i.get('name', '') or '') for i in left]
    right_names = [str(i.get('name', '') or '') for i in right]

    sim      = _match_items_semantic(left_names, right_names)
    strategy = 'semantic' if sim is not None else 'token'

    def score_of(li: int, ri: int) -> float:
        # The token score is a floor, not just a fallback: an exact-substring
        # match ("monitor" inside "Monitor, 24-inch LED") is unambiguous even
        # when the embedding is lukewarm about the extra words.
        tok = _token_overlap(left_names[li], right_names[ri])
        if sim is None:
            return tok
        return max(float(sim[li][ri]), tok)

    # Greedy best-first over all pairs: strongest pairings win regardless of
    # document order, so a reordered PR still matches its PPMP correctly.
    pairs = sorted(
        ((score_of(li, ri), li, ri) for li in range(len(left)) for ri in range(len(right))),
        key=lambda p: p[0],
        reverse=True,
    )

    taken_left, taken_right, chosen = set(), set(), {}
    for score, li, ri in pairs:
        if score < threshold:
            break
        if li in taken_left or ri in taken_right:
            continue
        chosen[li] = (ri, round(float(score), 4))
        taken_left.add(li)
        taken_right.add(ri)

    matches = []
    for li in range(len(left)):
        if li in chosen:
            ri, sc = chosen[li]
            matches.append({'left_index': li, 'right_index': ri, 'score': sc, 'matched': True})
        else:
            # Report the best score it *did* reach, so the caller can tell
            # "nothing like this at all" from "close, but under threshold".
            best = max((score_of(li, ri) for ri in range(len(right))), default=0.0)
            matches.append({'left_index': li, 'right_index': None, 'score': round(float(best), 4), 'matched': False})

    return jsonify({
        'strategy': strategy,
        'matches': matches,
        'unmatched_right': [ri for ri in range(len(right)) if ri not in taken_right],
    })


# ════════════════════════════════════════════════════════════════════════════════
# ENTRY POINT
# ════════════════════════════════════════════════════════════════════════════════

if __name__ == '__main__':
    print('=' * 55)
    print('  PRISM Market Scoping Microservice v2.0')
    print('  http://localhost:5001')
    print('=' * 55)
    print('  Loading sentence-transformers...')
    ok = _load_sentence_transformers()
    print(f'  Semantic AI : {"ENABLED (all-MiniLM-L6-v2)" if ok else "DISABLED -- pip install sentence-transformers torch"}')
    print(f'  Modes       : broad | specific | general')
    try:
        from waitress import serve
        print('  Server      : waitress')
        print('=' * 55)
        serve(app, host='127.0.0.1', port=5001, threads=8)
    except ImportError:
        print('  Server      : Flask dev server -- pip install waitress for production')
        print('=' * 55)
        app.run(host='127.0.0.1', port=5001, debug=False)
