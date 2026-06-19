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

Port: 5001
"""

# ── Standard library ──────────────────────────────────────────────────────────
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

# Natural language concepts precomputed for semantic value detection
_VALUE_CONCEPTS = [
    "includes additional items or accessories",
    "bundled with complementary products",
    "complete package with free extras",
    "more features and inclusions for the price",
    "better value with added bonuses",
]


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
    A product passes only when ALL per-spec cosine scores exceed the threshold.
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
        if all(score > threshold for score in per_spec_scores):
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
        'status':             'ok',
        'version':            '2.0',
        'modes':              ['specific', 'general', 'broad'],
        'semantic_available': _ST_AVAILABLE,
        'semantic_fallback':  'tfidf',
    })


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
            matched = results                              # nothing filtered — return all

    # ── MODE 2: General (product name, no specs) ──────────────────────────────
    else:
        matched = _semantic_rank(item, results)            # sentence_transformers
        if not matched:
            matched = _tfidf_rank(item, results)           # TF-IDF fallback
        if not matched:
            matched = results

    return jsonify({'mode': mode, 'matched': matched, 'count': len(matched)})


@app.route('/advantageous', methods=['POST'])
def advantageous():
    """
    Mark each result as advantageous based on budget and value signals.

    Rules:
      price <= budget           → always advantageous ("Within budget")
      budget < price <= +20%    → advantageous only if value signals detected
                                   (keyword signals OR semantic signals via sentence_transformers)
      price > budget * 1.20     → not advantageous

    Input:  { "budget": 500.00, "results": [...] }
    Output: same results, each gains is_advantageous (bool) and reason (str)
    """
    data    = request.get_json(silent=True) or {}
    budget  = float(data.get('budget', 0))
    results = data.get('results', [])

    if not results:
        return jsonify([])
    if budget <= 0:
        return jsonify(results)

    upper  = budget * 1.20
    output = []

    for r in results:
        price = float(r.get('price', 0))
        entry = {**r, 'is_advantageous': False, 'reason': ''}

        if price <= budget:
            entry['is_advantageous'] = True
            entry['reason']          = 'Within budget'

        elif price <= upper:
            over_amt = price - budget
            over_pct = (over_amt / budget) * 100
            combined = _product_text(r).lower()
            reasons  = []

            # Keyword-based value signals
            top_kw = [kw for kw in VALUE_KEYWORDS if kw in combined][:4]
            if top_kw:
                reasons.append(f"includes value-added items: {', '.join(top_kw)}")

            # Semantic value signals (only if sentence_transformers is loaded)
            elif _ST_AVAILABLE and _VALUE_CONCEPT_EMBEDDINGS is not None:
                emb       = _ST_MODEL.encode(combined, convert_to_tensor=True)
                sem_score = float(_ST_UTIL.cos_sim(emb, _VALUE_CONCEPT_EMBEDDINGS).max())
                if sem_score > 0.45:
                    reasons.append('description suggests additional inclusions beyond the base item')

            if reasons:
                entry['is_advantageous'] = True
                entry['reason']          = (
                    f"Only ₱{over_amt:,.2f} ({over_pct:.1f}%) over budget — "
                    + '; '.join(reasons)
                )

        output.append(entry)

    return jsonify(output)


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
    print('=' * 55)
    app.run(host='127.0.0.1', port=5001, debug=False)
