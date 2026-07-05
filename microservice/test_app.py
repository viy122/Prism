"""
Tests for the PRISM Market Scoping Microservice.

Run from the microservice directory:  python -m pytest test_app.py -q

The suite exercises the pure logic (mode detection, float parsing) and the
Flask endpoints via test_client. sentence-transformers is not required —
the endpoints are tested on their keyword / TF-IDF / broad fallback paths.
"""

import pytest

import app as m


@pytest.fixture()
def client():
    m._match_cache.clear()
    return m.app.test_client()


# ── _to_float ────────────────────────────────────────────────────────────────

@pytest.mark.parametrize('value, expected', [
    (500,          500.0),
    (99.5,         99.5),
    ('1,234.50',   1234.5),
    ('₱2,000',     2000.0),
    ('  750.00 ',  750.0),
    (None,         0.0),
    ('',           0.0),
    ('N/A',        0.0),
    ('free',       0.0),
])
def test_to_float(value, expected):
    assert m._to_float(value) == expected


# ── detect_search_mode ───────────────────────────────────────────────────────

@pytest.mark.parametrize('item, specs, expected', [
    # broad: every meaningful word is a category term
    ('office supplies',              [],        'broad'),
    ('laboratory equipment',         [],        'broad'),
    ('janitorial consumables',       [],        'broad'),
    ('mga kagamitan',                [],        'broad'),
    ('various items etc',            [],        'broad'),
    ('',                             [],        'broad'),
    ('school supplies and materials', ['a'],    'broad'),   # majority broad wins over specs
    # specific: concrete product + specs provided
    ('ballpen black 0.5mm',          ['black'], 'specific'),
    ('laptop core i7',               ['16gb'],  'specific'),
    # general: concrete product, no specs
    ('ballpen black 0.5mm',          [],        'general'),
    ('bond paper a4',                [],        'general'),
])
def test_detect_search_mode(item, specs, expected):
    assert m.detect_search_mode(item, specs) == expected


# ── /health ──────────────────────────────────────────────────────────────────

def test_health(client):
    data = client.get('/health').get_json()
    assert data['status'] == 'ok'
    assert set(data['modes']) == {'specific', 'general', 'broad'}


# ── /match ───────────────────────────────────────────────────────────────────

RESULTS = [
    {'name': 'Laptop Core i7 16GB RAM',  'price': 45000, 'snippet': 'gaming laptop'},
    {'name': 'Ballpen Black 0.5mm',      'price': 12,    'snippet': 'ballpoint pen'},
    {'name': 'Mouse Pad Large',          'price': 150,   'snippet': 'desk accessory'},
]


def test_match_empty_results(client):
    data = client.post('/match', json={'item': 'laptop', 'results': []}).get_json()
    assert data == {'mode': 'broad', 'matched': [], 'count': 0}


def test_match_broad_returns_all_position_ranked(client):
    data = client.post('/match', json={'item': 'office supplies', 'results': RESULTS}).get_json()
    assert data['mode'] == 'broad'
    assert data['count'] == len(RESULTS)
    scores = [r['match_score'] for r in data['matched']]
    assert scores == sorted(scores, reverse=True)


def test_match_always_includes_match_score(client):
    """Fallback paths must not return unscored results (frontend sorts on it)."""
    payload = {'item': 'laptop core i7', 'specs': ['zzzqqq'], 'results': RESULTS}
    data = client.post('/match', json=payload).get_json()
    assert data['count'] > 0
    assert all('match_score' in r for r in data['matched'])


def test_match_keyword_filtering(client):
    payload = {'item': 'ballpen black 0.5mm', 'specs': ['ballpen', 'black'], 'results': RESULTS}
    data = client.post('/match', json=payload).get_json()
    assert data['mode'] == 'specific'
    assert data['matched'][0]['name'] == 'Ballpen Black 0.5mm'


def test_match_caches_response(client):
    payload = {'item': 'ballpen', 'results': RESULTS}
    first  = client.post('/match', json=payload).get_json()
    assert len(m._match_cache) == 1
    second = client.post('/match', json=payload).get_json()
    assert first == second


def test_match_cache_evicts_oldest(client):
    m._match_cache.clear()
    for i in range(m._CACHE_LIMIT + 5):
        m._match_cache[f'key-{i}'] = {'mode': 'broad', 'matched': [], 'count': 0}
        if len(m._match_cache) > m._CACHE_LIMIT:
            m._match_cache.pop(next(iter(m._match_cache)))
    client.post('/match', json={'item': 'ballpen', 'results': RESULTS})
    assert len(m._match_cache) <= m._CACHE_LIMIT
    assert 'key-0' not in m._match_cache


# ── /advantageous ────────────────────────────────────────────────────────────

def test_advantageous_no_budget_passthrough(client):
    rows = client.post('/advantageous', json={'budget': 0, 'results': RESULTS}).get_json()
    assert all('is_advantageous' not in r for r in rows)


def test_advantageous_rules(client):
    budget  = 500
    results = [
        {'title': 'Within',        'price': 450},
        {'title': 'No price',      'price': None},
        {'title': 'String price',  'price': '480.00'},
        {'title': 'Printer bundle with free ink', 'price': 600},   # +20%, keyword signal
        {'title': 'Plain overpriced item',        'price': 620},   # +24%, no signal
        {'title': 'Far over',      'price': 900},                  # beyond tolerance
    ]
    rows = client.post('/advantageous', json={'budget': budget, 'results': results}).get_json()
    by_title = {r['title']: r for r in rows}

    assert by_title['Within']['is_advantageous'] is True
    assert by_title['Within']['reason'] == 'Within budget'
    assert by_title['No price']['is_advantageous'] is False
    assert by_title['String price']['is_advantageous'] is True
    assert by_title['Printer bundle with free ink']['is_advantageous'] is True
    assert 'over budget' in by_title['Printer bundle with free ink']['reason']
    assert by_title['Plain overpriced item']['is_advantageous'] is False
    assert by_title['Far over']['is_advantageous'] is False


def test_advantageous_tolerance_boundary(client):
    budget = 100
    edge   = budget * (1 + m.OVER_BUDGET_TOLERANCE)
    results = [
        {'title': 'At edge with kit inclusion', 'price': edge},
        {'title': 'Just past edge with kit',    'price': edge + 1},
    ]
    rows = client.post('/advantageous', json={'budget': budget, 'results': results}).get_json()
    assert rows[0]['is_advantageous'] is True
    assert rows[1]['is_advantageous'] is False
