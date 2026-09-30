#!/usr/bin/env python3
"""Build an installable nada.zip without source tooling or premium plugins."""
import argparse
import re
import zipfile
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument('--version', default='1.0.0')
parser.add_argument('--output', default='dist/nada.zip')
args = parser.parse_args()
if not re.fullmatch(r'\d+\.\d+\.\d+', args.version):
    raise SystemExit('Version must be major.minor.patch')
root = Path(__file__).resolve().parents[1]
output = root / args.output
output.parent.mkdir(parents=True, exist_ok=True)
allowed_dirs = {'assets', 'inc', 'template-parts', 'page-templates', 'data', 'languages'}
with zipfile.ZipFile(output, 'w', zipfile.ZIP_DEFLATED) as package:
    for path in sorted(root.rglob('*')):
        relative = path.relative_to(root)
        if not path.is_file() or (len(relative.parts) > 1 and relative.parts[0] not in allowed_dirs):
            continue
        if len(relative.parts) == 1 and path.suffix not in {'.php', '.css', '.png', '.txt'}:
            continue
        data = path.read_bytes()
        if relative.as_posix() == 'style.css':
            data = re.sub(rb'(?m)^Version: .+$', ('Version: ' + args.version).encode(), data)
        package.writestr('nada/' + relative.as_posix(), data)
print(output)
