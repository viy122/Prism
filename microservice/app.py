#!/usr/bin/env python3
"""
PRISM Market Scoping Microservice
AI-powered spec matching and budget-aware decision support.
Runs on http://localhost:5001

Endpoints:
  GET  /health           - Service health + AI status
  POST /match            - Filter & score results against procurement specs
  POST /advantageous     - Flag budget-advantageous items
"""

import json
import os
import re

from flask import Flask, request, jsonify

# Load .env from this directory if present
try:
    from dotenv import load_dotenv
    load_dotenv(os.path.join(os.path.dirname(__file__), '.env'))
except ImportError:
    pass

app = Flask(__name__)


# ── Helpers ──────────────────────────────────────────────────────────────────

def _get_client():
    """Return an Anthropic client when ANTHROPIC_API_KEY is set, else None."""
    key = os.environ.get('ANTHROPIC_API_KEY', '').strip()
    if not key:
        return None
    try:
        import anthropic
        return anthropic.Anthropic(api_key=key)
    except ImportError:
        return None


def _extract_json_array(text: str) -> list:
    """Robustly extract the first JSON array from an LLM response."""
    text = text.strip()
    m = re.search(r'\[[\s\S]*?\]', text)
    if m:
        return json.loads(m.group())
    return []


def _keyword_score(specs: list[str], title: str, desc: str) -> float:
    """Simple keyword overlap — used when AI is unavailable."""
    if not specs:
        return 1.0
    combined = (title + ' ' + desc).lower()
    matched = sum(1 for s in specs if s.lower() in combined)
    return round(matched / len(specs), 2)


# ── Routes ───────────────────────────────────────────────────────────────────

@app.route('/health', methods=['GET'])
def health():
    client = _get_client()
    return jsonify({
        'status':       'ok',
        'service':      'PRISM Market Scoping Microservice v1.0',
        'ai_available': client is not None,
    })


@app.route('/match', methods=['POST'])
def match_specs():
    """
    Input:  { specs: ["ballpoint","black","0.5mm"], results: [{title, description, price, ...}] }
    Output: filtered results sorted by match_score desc, each item gains a `match_score` field (0.0–1.0).
    """
    data    = request.get_json(silent=True) or {}
    specs   = [str(s).strip() for s in data.get('specs', []) if str(s).strip()]
    results = data.get('results', [])

    if not results:
        return jsonify([])
    if not specs:
        return jsonify(results)

    client = _get_client()

    # ── AI path ──────────────────────────────────────────────────────────────
    if client:
        try:
            compact = [
                {
                    'i': i,
                    't': r.get('title', r.get('name', ''))[:120],
                    'd': (r.get('description', r.get('snippet', '')) or '')[:200],
                    'p': r.get('price', 0),
                }
                for i, r in enumerate(results)
            ]

            prompt = (
                "Procurement spec matcher for Philippine government purchasing.\n\n"
                f"Required specifications: {', '.join(specs)}\n\n"
                "Products (JSON):\n"
                f"{json.dumps(compact, ensure_ascii=False)}\n\n"
                "Score each product 0.0–1.0 based on how well it satisfies ALL specifications.\n"
                "1.0 = perfect match, 0.5 = partial, 0.0 = does not match.\n"
                "Omit any product scoring below 0.35.\n"
                "Sort by score descending.\n\n"
                "Reply ONLY with a compact JSON array — no explanation:\n"
                '[{"i":0,"s":0.95},{"i":2,"s":0.70}]'
            )

            msg = client.messages.create(
                model='claude-haiku-4-5-20251001',
                max_tokens=400,
                messages=[{'role': 'user', 'content': prompt}],
            )

            scored = _extract_json_array(msg.content[0].text)

            output = []
            for entry in scored:
                idx   = entry.get('i')
                score = float(entry.get('s', 0))
                if idx is not None and 0 <= idx < len(results):
                    output.append({**results[idx], 'match_score': score})

            if output:
                return jsonify(output)
            # If AI returned empty (over-filtered), fall through to keyword

        except Exception:
            pass  # degrade gracefully to keyword matching

    # ── Keyword fallback ─────────────────────────────────────────────────────
    scored = []
    for r in results:
        score = _keyword_score(
            specs,
            r.get('title', r.get('name', '')),
            r.get('description', r.get('snippet', '')),
        )
        if score >= 0.35:
            scored.append({**r, 'match_score': score})

    scored.sort(key=lambda x: x['match_score'], reverse=True)
    return jsonify(scored if scored else results)


@app.route('/advantageous', methods=['POST'])
def advantageous():
    """
    Input:  { budget: 500.00, results: [{title, price, description, ...}] }
    Output: same results, each gains `is_advantageous` (bool) and `reason` (str).

    Rules:
      - price <= budget                → advantageous ("Within budget")
      - budget < price <= budget×1.20  → AI decides based on quality/specs
      - price > budget×1.20            → not advantageous
    """
    data    = request.get_json(silent=True) or {}
    budget  = float(data.get('budget', 0))
    results = data.get('results', [])

    if not results:
        return jsonify([])
    if budget <= 0:
        return jsonify(results)

    client = _get_client()

    # ── AI path ──────────────────────────────────────────────────────────────
    if client:
        try:
            compact = [
                {
                    'i': i,
                    't': r.get('title', r.get('name', ''))[:120],
                    'p': r.get('price', 0),
                    'd': (r.get('description', r.get('snippet', '')) or '')[:150],
                }
                for i, r in enumerate(results)
            ]

            prompt = (
                "Philippine government procurement budget advisor.\n\n"
                f"Unit budget: PHP {budget:,.2f}\n\n"
                "Products:\n"
                f"{json.dumps(compact, ensure_ascii=False)}\n\n"
                "For each product decide:\n"
                "  - is_advantageous = true  if price <= budget, OR price is ≤20% over budget\n"
                "    AND the product clearly offers superior quality, specs, or bundled value.\n"
                "  - reason = one short sentence (empty string if not advantageous).\n\n"
                "Reply ONLY with compact JSON — no explanation:\n"
                '[{"i":0,"a":true,"r":"Within budget"},{"i":1,"a":false,"r":""}]'
            )

            msg = client.messages.create(
                model='claude-haiku-4-5-20251001',
                max_tokens=400,
                messages=[{'role': 'user', 'content': prompt}],
            )

            flags    = _extract_json_array(msg.content[0].text)
            flag_map = {f['i']: f for f in flags}

            output = []
            for i, r in enumerate(results):
                f = flag_map.get(i, {'a': False, 'r': ''})
                output.append({
                    **r,
                    'is_advantageous': bool(f.get('a', False)),
                    'reason':          str(f.get('r', '')),
                })
            return jsonify(output)

        except Exception:
            pass  # degrade to rule-based

    # ── Rule-based fallback ───────────────────────────────────────────────────
    output = []
    for r in results:
        price = float(r.get('price', 0))
        if price <= budget:
            output.append({**r, 'is_advantageous': True,  'reason': 'Within budget'})
        elif price <= budget * 1.20:
            output.append({**r, 'is_advantageous': True,  'reason': 'Slightly over budget but may offer better value'})
        else:
            output.append({**r, 'is_advantageous': False, 'reason': ''})

    return jsonify(output)


# ── Entry point ───────────────────────────────────────────────────────────────

if __name__ == '__main__':
    api_key = os.environ.get('ANTHROPIC_API_KEY', '').strip()
    print('=' * 55)
    print('  PRISM Market Scoping Microservice')
    print('  http://localhost:5001')
    print('=' * 55)
    print(f'  AI (Claude Haiku): {"ENABLED" if api_key else "DISABLED"}')
    if not api_key:
        print('  -> Add ANTHROPIC_API_KEY to microservice/.env')
        print('     to enable intelligent spec matching.')
    print('=' * 55)
    app.run(host='127.0.0.1', port=5001, debug=False)
