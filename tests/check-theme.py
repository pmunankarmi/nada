"""Check package data, template references, and editor-content boundaries."""
import json
import re
from pathlib import Path

root = Path(__file__).resolve().parents[1]
media = json.loads((root / 'data/media-manifest.json').read_text())['files']
php_files = list(root.glob('*.php')) + list((root / 'inc').glob('*.php')) + list((root / 'template-parts').rglob('*.php')) + list((root / 'page-templates').glob('*.php'))
for path in php_files:
    text = path.read_text()
    assert '@package ' in text, f'Missing package comment: {path}'
    assert 'NADA' in text, f'Missing theme attribution: {path}'
    assert 'NADAPHPTOKEN' not in text, f'Unexpanded template token: {path}'
    for part in re.findall(r"get_template_part\( '([^']+)'", text):
        if part.endswith('/'):
            continue
        assert (root / (part + '.php')).exists(), f'Missing part: {part}'
    for asset in re.findall(r"nada_asset\( '([^']+)'", text):
        assert (root / asset).exists() or asset in media, f'Missing artwork: {asset}'
fields = json.loads((root / 'data/page-fields.json').read_text())
for field in fields.values():
    assert field['type'] in {'text', 'textarea'}
    assert not re.search(r'<[^>]+>', field['default']), 'Markup in custom field default'
library = json.loads((root / 'data/library.json').read_text())
assert len(library['KORA']['RECIPES']) == 19
for recipe in library['KORA']['RECIPES']:
    assert recipe['id'] in library['KORA_AR']['recipes'], 'Missing Arabic recipe'
    for key in ['image', 'imageAr']:
        assert recipe.get(key, recipe['image']) in media, f'Missing recipe upload manifest: {recipe.get(key)}'
assert len(json.loads((root / 'data/products.json').read_text())) == 13
assert not re.search(r"'type'\s*=>\s*'(wysiwyg|gallery)'", (root / 'inc/fields.php').read_text())
print(f'Passed: {len(php_files)} PHP headers, template references, 19 bilingual recipes, 13 products, text-only fields.')

for asset in media:
    assert not (root / asset).exists(), f'Admin-editable image still in theme: {asset}'
