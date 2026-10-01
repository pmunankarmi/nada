"""HTTP smoke checks against a disposable local WordPress install only."""
import re
import sys
import urllib.parse
import urllib.request

base = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost:8098'
assert urllib.parse.urlparse(base).hostname in {'localhost', '127.0.0.1'}, 'Run only against disposable local WordPress.'
paths = {
    '/?smoke=1': 'Greek Wins',
    '/ar/': 'اليوناني',
    '/why-greek/': 'More Than',
    '/ar/why-greek/': 'dir="rtl"',
    '/recipes/': 'recipe-grid',
    '/ar/recipes/': 'dir="rtl"',
    '/products/': 'sku__name',
    '/ar/products/': 'dir="rtl"',
    '/recipes/protein-coleslaw/': 'High Protein Coleslaw',
    '/ar/recipes/protein-coleslaw/': 'dir="rtl"',
    '/products/plain-full/': 'Plain',
    '/recipe-category/breakfast/': 'Breakfast',
    '/share-recipe/': 'nada_nonce',
    '/?s=coleslaw&post_type=recipe': 'High Protein Coleslaw',
}
for path, expected in paths.items():
    with urllib.request.urlopen(base + path) as response:
        html = response.read().decode()
        assert response.status == 200
        assert expected in html, f'{path}: missing {expected}'
        assert 'There has been a critical error' not in html
        assert 'NADAPHPTOKEN' not in html
        if path in {'/products/', '/ar/products/'}:
            assert len(re.findall(r'<article[^>]*data-categories=', html)) == 13
            assert 'skufilter' in html and 'nada-search' not in html
            assert not re.search(r'<a[^>]+href="[^"]+/products/[^"]+', html)
        if path in {'/recipes/', '/ar/recipes/'}:
            assert len(re.findall(r'<dialog ', html)) == 19
            assert 'data-recipe-search' in html and 'modal__steps' in html
            assert 'data-page="recipes"' in html
        assert not re.search(r'<(?:b|br)\s*/?>\s*(?:Warning|Fatal error)', html)
    print('PASS', path)
html = urllib.request.urlopen(base + '/share-recipe/').read().decode()
nonce = re.search(r'name="nada_nonce" value="([^"]+)"', html)[1]
page_id = re.search(r'name="return_id" value="([^"]+)"', html)[1]
category = re.search(r'name="category" value="(\d+)"', html)[1]
data = dict(action='nada_submit_recipe', nada_nonce=nonce, return_id=page_id, category=category,
            language='en', recipe='Local integration recipe', ingredients='Yogurt\nHoney',
            method='Mix gently.\nServe.', email='test@example.test', website='')
request = urllib.request.Request(base + '/wp-admin/admin-post.php', urllib.parse.urlencode(data).encode())
with urllib.request.urlopen(request) as response:
    assert 'recipe-status=success' in response.url, response.url
print('PASS private form submission accepted')
data['nada_nonce'] = 'invalid'
request = urllib.request.Request(base + '/wp-admin/admin-post.php', urllib.parse.urlencode(data).encode())
with urllib.request.urlopen(request) as response:
    assert 'recipe-status=error' in response.url
print('PASS invalid nonce rejected')
