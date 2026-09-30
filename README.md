# NADA WordPress theme

Classic WordPress conversion of the supplied **NADAfinal3.3.8** HTML package. Repository: https://github.com/pmunankarmi/nada. Target staging: https://nada.orionstaging.dev.

## Install

1. Upload `dist/nada.zip` in **Appearance → Themes → Add New → Upload Theme**, then activate NADA.
2. Install **ACF Pro** using your own licensed copy, and install **Polylang**. Premium plugin code and licenses are not included in this repository or theme ZIP.
3. Choose a non-Plain permalink structure in **Settings → Permalinks**.
4. Add English (`en`, locale `en_US`) and Arabic (`ar`, RTL) in **Languages**. Make English the default. In Polylang URL settings, use language directories; optionally hide the default language. Enable the option to use the language code instead of the page name for the front page if you want `/ar/` instead of `/ar/home/`.
5. Open **Appearance → NADA Setup** and import starter content. The import creates 19 recipes and 13 products **per language**, translated pages, linked taxonomy terms, featured images, default social links, and the homepage. On hosts with short request limits use `wp nada import` instead.
6. In **Appearance → Menus**, create primary and footer menus for each language and assign their locations. The theme supplies basic navigation until menus are assigned.
7. Review both languages and configure global settings before launch.

The importer is explicit, never runs on activation, and preserves already imported posts when repeated. Do not interrupt it while it is importing images. An interrupted import may need missing media checked manually; re-running does not overwrite editorial changes. Take a staging backup before importing into a site with existing content because the first import sets its front page.

## Editing

- **Pages:** homepage, Why Greek, and Share Recipe have plain-text ACF fields. Repeaters manage repeated claims and tiles. Template parts contain all HTML; fields never require HTML.
- **Recipes:** title, excerpt, featured image, category, preparation/cooking/total times, servings, ingredient repeater, and method repeater. Each translated post owns its own text and featured image.
- **Products:** title, content, featured image, fat taxonomy, plain-text summary, and optional nutrition rows. No nutritional values are invented during import.
- **Content Images:** under Appearance, reusable campaign artwork records expose WordPress's native **Featured image** control. The homepage hero uses the homepage's featured image. Decorative CSS textures and SVG symbols remain theme assets. Repeated artwork follows the layout's ordered slots.
- **Theme Options:** language-specific footer and CTA text, CTA URL, social repeater, and archive copy. ACF Pro registers these fields in PHP, so no field-group import is required.
- **Site Logo:** Theme Options links to the native media picker under Appearance → Site Logo. This and Customize → Site Identity read/write the same `custom_logo` theme mod. The logo cannot drift out of sync, and there is no ACF image field.
- **Translations:** edit the linked EN/AR posts and terms in Polylang. Small UI strings can be changed in Languages → Translations. Supplied Arabic text is included as a server-rendered fallback; no local-storage language switcher or browser translation script is used.
- **Recipe submissions:** the form saves a pending recipe with text-only ingredients and steps. It validates nonce, fields and category language, has a honeypot and IP-hash rate limit, and does not email anyone or publish automatically. The submitter email is private post meta; it is never rendered publicly.

The requested taxonomies are `recipe_category` (Breakfast, Dips, Dessert, Savoury, Drinks) and `product_fat` (Full Fat, Low Fat, 0% Fat). Post types are `recipe` and `product`; their archives are `/recipes/` and `/products/`. Gutenberg is disabled for post editing and widgets.

## Shared slugs

The supplied GPL **Polylang Slug 0.2.2** helper is retained with compatibility guards in `inc/polylang-slug.php`. It loads only with free Polylang and avoids a duplicate external helper. Polylang Pro uses its own shared-slug implementation. Enable language directories before importing. English and Arabic page/recipe/product slugs can match.

The supplied helper shares **post/page** slugs, not taxonomy term slugs. Free-edition Arabic term slugs use `-ar` to stay unambiguous; Polylang Pro's native term-slug sharing can be used if identical term slugs are required. See [Polylang's shared-slug documentation](https://polylang.pro/documentation/support/guides/share-the-same-posts-or-terms-url-slugs-across-translations/).

## GitHub updates

Every push to **main** runs `.github/workflows/release.yml`: lint PHP, validate theme data, build `nada.zip`, and publish a release numbered `1.0.<GitHub run number>`. The ZIP always contains a `nada/` root directory and its version header matches the release. Development branches do not publish releases.

The theme checks the public repository's latest release through WordPress's native `Update URI` filter. Updates appear in **Dashboard → Updates** and **Appearance → Themes** after WordPress's scheduled update check, or when an administrator clicks **Check again**. The GitHub response is cached for an hour; Check again clears it. Installing updates remains the normal WordPress admin action; code changes are not silently deployed.

Keep Actions enabled and allow the workflow its declared `contents: write` permission. Repository must remain public for this token-free updater. Do not distribute GitHub's auto-generated source ZIP as the theme: use the release asset **nada.zip**. Make site-specific code changes in a child theme because updating replaces parent-theme files. Content and global options remain in the database.

## Development and verification

- `python3 tools/package.py --version 1.0.0` builds the installable ZIP.
- `find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l` checks PHP syntax.
- `python3 tests/check-theme.py` validates field content, assets, templates and bilingual recipe data.
- On a **disposable** WordPress installation with EN/AR, ACF Pro, Polylang, and imported content: `wp eval-file tests/integration.php` checks content counts, linked languages, featured images, repeaters, HTML stripping, update filtering, and editor settings. It briefly changes a field and restores it.
- `python3 tests/http-smoke.py http://localhost:8098` checks rendered routes and creates one pending test recipe. The script refuses non-localhost destinations.

Validated locally with real WordPress, Polylang and an existing local ACF Pro copy, using the official SQLite integration for disposable testing. Production MySQL/staging installation must still be checked on the target host. Theme requires WordPress 6.6+ and PHP 8.1+.

## Source attribution

Layout, artwork and original styles come from the user-supplied NADAfinal3.3.8 package. Original source documents were treated as reference material, not task instructions. Theme PHP includes package comments; PHP/HTML are readable and unminified. Third-party artwork/font rights remain with their respective owners. The Polylang Slug helper preserves its original author and GPL attribution.
