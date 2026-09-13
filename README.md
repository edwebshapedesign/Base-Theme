# Base Theme

A custom WordPress theme by [Webshape Design](https://webshapedesign.co.uk/). Built around ACF Blocks, an options-driven header and footer, and a set of hand-written archive, search and single-post templates. The markup originates from a Webflow export (Webflow `data-wf-*` attributes and `w-` utility classes are still present) and has been reworked into a native WordPress theme.

> **Licence / usage:** Copyright Webshape Design. No unauthorised changes without written permission (see `style.css` header).

- **Version:** 1.0.0
- **Repository:** `git@github.com:edwebshapedesign/Base-Theme.git`
- **Template folder:** `base-theme`

## Requirements

- WordPress (classic + block editor)
- **Advanced Custom Fields PRO** — the theme leans heavily on ACF for blocks, options and repeaters. It will not function without it.
- **Contact Form 7** — the footer listens for `wpcf7mailsent` and redirects on submission.
- **Yoast SEO** — `functions.php` filters the Yoast canonical for posts (optional, but the filter targets it by name).

The theme registers its own ACF options menu (**Site Content**, with **General** and **Scripts** sub-pages), so no manual `acf_add_options_page()` call is needed.

## Structure

```
base-theme/
├── style.css              Theme header + design tokens / reset / .container
├── functions.php          Setup, asset enqueue, ACF options, rewrites, helpers
├── blocks.php             ACF block registration + block category + theme_image() (included by functions.php)
├── inc/integrations.php   GTM, Umami, schema JSON-LD, custom scripts (Site Content → Scripts)
├── index.php              Blog archive — post grid + contact form
├── page.php               Generic page — runs the_content() (renders ACF blocks)
├── single-post.php        Single blog post (intro hero, author profile via ACF)
├── category.php           Category archive (archive-grid / archive-card via The Loop)
├── tag.php                Tag archive
├── search.php             Search results across public post types
├── 404.php                Friendly not-found page with search + quick links
├── header.php             <head>, ACF header/body scripts, Organization schema, nav part
├── footer.php             Footer columns, socials (masked SVG icons), CF7 redirect
├── author.php / home.php  Present but currently empty
│
├── acf-json/              ACF local JSON — one file per field group (incl. block field groups)
│
├── components/
│   ├── backgrounds.php    Background variants (full / gradient / dot patterns)
│   ├── buttons.php        ACF buttons repeater → styled <a class="btn …">
│   └── blocks/
│       ├── parts/nav.php  Primary navigation template part (ACF options-driven)
│       └── <name>/        One folder per block (auto-registered from block.json)
│           ├── block.json   Metadata, ACF settings, asset declarations
│           ├── render.php   Render template
│           ├── style.css    Front-end + editor
│           ├── view.css     Front-end only
│           ├── editor.css   Editor only
│           └── view.js      Front-end only (deferred)
│
├── css/                   Global (site-wide) styles only
│   ├── main.css           Design tokens, reset, .container
│   ├── fonts.css          @font-face — Montserrat + Source Sans 3 (local)
│   ├── additional.css     Component tweaks / responsive overrides
│   └── entrance-animations.css
│
├── js/                    Global (site-wide) scripts only
│   ├── main.js            App entry
│   ├── additional.js      jQuery-scoped entry
│   ├── entrance-animations.js
│   ├── acf-inline-editor.js  Optional editor-only helper (enqueued only if present)
│   ├── jquery-3.5.1.min.js   Bundled jQuery (replaces core jQuery)
│   └── hls.min.js         HLS.js — for streamed/adaptive video
│
├── fonts/                 Local font files (Montserrat, Source Sans 3) — see fonts.css
├── images/                Theme images (favicon, webclip, icons, …)
└── videos/                Video assets
```

## How it works

### Theme setup (`functions.php`)

On `after_setup_theme` the theme enables `title-tag`, `automatic-feed-links`, `post-thumbnails` and HTML5 `search-form`, and registers a single **Main Menu** nav location (`main-menu`).

`functions.php` also:

- **Includes `blocks.php`** — this is what activates all ACF block registration (see below).
- **Registers the ACF options pages**: a `Site Content` parent menu (capability `manage_options`) with sub-pages **General** (`site-content-general`) and **Scripts** (`site-content-scripts`). All sub-pages share the `option` post ID, so templates read any field via `get_field( '…', 'option' )`. Add a section by adding another `acf_add_options_sub_page()` and pointing a field group's `options_page` location rule at its slug.
- **Includes `inc/integrations.php`** — everything on **Site Content → Scripts**: Google Tag Manager (`gtm_enabled`, `gtm_id`, validated and normalised on save), Umami analytics (`umami_enabled`, `umami_website_id`, `umami_script_url`, `umami_domains`), Organization + WebSite JSON-LD schema (`schema_*`, extend via the `theme_schema_graph` filter), and raw custom snippets (`header_scripts`, `body_scripts`, `footer_scripts`). Output order in `<head>`: custom header scripts (1) → GTM (2) → Umami (3) → schema (5), so consent managers pasted into header scripts run before GTM. `tracking_exclude_logged_in` skips GTM/Umami for logged-in users. Nothing outputs in the editor, on previews or in the Customizer. Templates never echo these directly; `header.php` calls `wp_body_open()` and `body_class()` so tag-manager and consent plugins work too.
- Enqueues the **global** styles and scripts only. Cache-busting uses `theme_asset_version()` (file modified time), so there are no hand-edited `?v=` strings. Block-specific CSS/JS lives with each block (see below) and must not be added here.
- **Replaces core jQuery** with the bundled `js/jquery-3.5.1.min.js`, loaded in the footer, and enqueues `main.js`, `hls.min.js`, `additional.js` and `entrance-animations.js` on top of it.
- Loads `acf-inline-editor.js` in the block editor only, and only if the file exists.

### ACF Blocks

`blocks.php` drives page building. On `init` it globs `components/blocks/*/block.json` and passes each folder to `register_block_type()`. ACF reads the `acf` key from `block.json`, so no `acf_register_block_type()` call is needed. Blocks are grouped into two custom categories defined in `blocks.php` — **Header** (`theme-header`, for heroes and page headers) and **Blocks** (`theme-blocks`, everything else). Set `"category"` in each `block.json` accordingly. `allowed_block_types_all` is filtered so **only** these theme blocks appear in the editor — core blocks are hidden by design.

**Adding a block** is just adding a folder:

```
components/blocks/<name>/
├── block.json     name ("acf/<name>"), title, category, acf settings, asset declarations
├── render.php     render template
├── style.css      loaded on the front-end AND in the editor
├── view.css       front-end only
├── editor.css     editor only — never shipped to visitors
└── view.js        front-end only, deferred by core
```

Then add a field group in `acf-json/` with a `block == acf/<name>` location rule (or create it in the ACF UI and it will be saved there automatically).

One reference block ships with the theme:

- `components/blocks/text_block/` — the `text_block` pattern from the block library and the worked example for every convention below. Fields: `title` (H2), `post_title` (highlighted intro line), `text` (WYSIWYG), `bullets` repeater (`bold` + `text`, rendered as a ticked checklist), `buttons` repeater via `components/buttons.php`, `layout` select and an `image` (column layouts only), plus a `background` choice (White / Light grey / Dark) and the cloned Block Settings. The six layouts are `simple`, `simple-columns` (text flows into two CSS columns), `columns` / `columns-reverse` (image beside text) and `bk-columns` / `bk-columns-reverse` (full-height background image spacer beside text). Field group: `acf-json/group_block_text_block.json`.

The site header lives in `components/blocks/parts/nav.php` (called from `header.php`): logo from **Site Content → General → Branding** (`site_logo`, linked home, alt from the Media Library or the site name) and the **Main Menu** location rendered with `wp_nav_menu()` to two levels. With no menu assigned it lists top-level pages and shows editors a hint. Styles are in `css/navigation.css`, the mobile toggle in `js/navigation.js` (no jQuery), both enqueued globally.

**Block Settings on any block (visibility + padding).** `acf-json/group_block_settings.json` is a clone source (inactive, never shown on its own) with a `block_visible` true/false ("Show block on the front end", default on) and `padding_top` / `padding_bottom` selects (None → Extra large). To add it to a block: in the block's field group add a **Clone** field → choose "Block Settings (clone source)", display **Seamless**; then in `render.php` build the wrapper with `get_block_wrapper_attributes( [ 'class' => theme_block_settings_classes( 'my-block' ) ] )`. That outputs `has-pt-*` / `has-pb-*` classes which map to the `--space-section-*` scale in `css/main.css`. Don't set vertical padding on the wrapper in the block's own CSS. Visibility needs nothing in `render.php`: `blocks.php` filters `pre_render_block` and skips any `acf/*` block whose toggle is off on the front end, while in the editor the block still renders, dimmed with a "Hidden on the front end" badge (`css/editor.css`, loaded via `enqueue_block_editor_assets` in `blocks.php`). `theme_block_spacing_classes()` remains as an alias of the new helper. The text block is the worked example. Add more shared settings (e.g. background colour) to the same clone group and they appear on every block that clones it.

Global design tokens (`--color-*`, `--space-section-*`, `--font-*`, `--radius-*`) and the shared `.btn` styles live in `css/main.css`. Blocks reference tokens with fallbacks, e.g. `var(--color-dark, #111827)`.

**Entrance animations** are shared, not per block. Add `data-animate` to any element in a block's `render.php` and it fades/rises in when scrolled into view, staggered against its siblings. `css/entrance-animations.css` and `js/entrance-animations.js` handle the rest; `header.php` adds the `has-anim` class in `<head>` so nothing flashes before the footer script runs. Reduced-motion users and browsers without IntersectionObserver see everything immediately. Do not add per-block `view.js` files for this.

Key conventions:

- **Assets are per block.** WordPress only enqueues a block's `style` / `viewStyle` / `viewScript` on pages where that block is present. Do not add block styles to the global CSS in `css/` — that defeats the conditional loading.
- **Use `get_block_wrapper_attributes()`** for the outer element in `render.php`. It wires up alignment, anchor, custom class names and block-supports styles the way core blocks do.
- Set `"mode": "preview"` and `"blockVersion": 3` in `block.json` so editors see a live preview.
- Every asset path in `block.json` uses `file:./…` — WordPress resolves it relative to the folder. The `version` field in `block.json` is used as the cache-busting string for that block's assets, so bump it when you change the block's CSS/JS.
- **Inserter preview image.** To show a static screenshot when the block is hovered in the inserter, add to `block.json`:

  ```json
  "example": {
    "attributes": {
      "mode": "preview",
      "data": { "preview_image_help": "preview.png" }
    }
  }
  ```

  and at the top of `render.php` check `$block['data']['preview_image_help']`, echo the image and `return` (see `text_block/render.php`). Drop a `preview.png` (about 1200×700) into the block folder. The ACF docs show this as a PHP array; in `block.json` it is plain JSON as above.

Pages are assembled by dropping blocks into the editor; `page.php` calls `the_content()` and each block renders itself (including any JSON-LD schema it outputs). `theme_image( $id, $classes )` is a shared helper returning a responsive `<img>` with `srcset`/`sizes` from an attachment ID.

### Header, footer & navigation

`header.php` and `footer.php` are almost entirely ACF-options-driven — script slots, logos, footer columns and links, social icons and awards all come from option fields. Social icons render as CSS-masked SVGs (per-icon `mask-image` injected inline). `header.php` outputs an `Organization` JSON-LD schema block, optionally extended with a `memberOf` value via a theme global. Navigation lives in `components/blocks/parts/nav.php` with a lightweight active-link helper.

### Templates

`template-developer-guide.php` ("Developer Guide" in the page template dropdown) renders `CREATING-A-BLOCK.md` as a page for the team: a sticky contents list from the headings, and the markdown converted at request time by `inc/markdown.php` (a deliberately tiny converter for the theme's own docs, not for user content). Edit the `.md` and the page updates. Only users who can edit posts see it; visitors get a sign-in prompt. Styles are in `css/developer-guide.css`, loaded on that template only.

The archive/search/404 templates (`category.php`, `tag.php`, `search.php`, `404.php`) share a common `page-hero` + `archive-grid` / `archive-card` pattern so every listing matches. `single-post.php` builds a post hero from an ACF `intro` field and an author profile from user-level ACF fields (`author_photo`, `author_bio`, `author_role`).

### Blog permalinks (`/blog/…`)

Posts are forced under a `/blog/` prefix. `functions.php` rewrites `post_link` / `post_type_link` to `home_url( '/blog/{slug}/' )`, adds matching `add_rewrite_rule()` entries (single posts and paged archives), flushes rules on theme activation/deactivation, and overrides the Yoast canonical for singular posts to match. **After deploying, visit Settings → Permalinks once (or re-activate the theme) to flush rewrite rules.**

### Other helpers in `functions.php`

- `numeric_posts_nav()` — numbered pagination output (`page-selector-link` markup).
- `nav_to_array()` / `buildTree()` — turns a WP menu into a nested array for custom nav rendering.
- Custom image sizes: `top-content` (1080×720) and `gallery-thumbnail` (149×438), with `top-content` exposed in the media picker.
- `insertImageToPost()` — sideloads a remote image URL into the media library and returns the attachment ID.
- `[pageurl]` shortcode — outputs the current page URL.
- YouTube embeds rewritten to `youtube-nocookie.com`.
- Read-more / excerpt links reduced to `...`.
- Dashicons dequeued for logged-out visitors; XML-RPC disabled; native lazy-loading forced on; captions stripped from `img_caption_shortcode`; `wpautop` removed from ACF content and CF7.

## Getting started

1. Copy the theme into `wp-content/themes/` (folder named `base-theme`).
2. Install and activate **ACF PRO** and **Contact Form 7** (and **Yoast SEO** if you want the canonical filter to apply).
3. Activate the theme — this registers the **Site Content** options page and flushes the `/blog/` rewrite rules.
4. Populate the **Site Content** options (header nav, footer columns, socials, awards, business description, archive images) and the per-block / post-author ACF field groups.
5. If post URLs 404, re-save **Settings → Permalinks** to flush rewrite rules.

## Notes

- **Empty asset folders:** `fonts/` / `images/` / `videos/` are empty in this checkout. `fonts.css` references local font files, so add them before deploying.
- Markup carries Webflow artefacts (`w-container`, `wf-section`, `data-wf-*`) — harmless, can be cleaned up over time.
- `header.php` contains a global `whenClicked`-attribute `eval()` handler inherited from the export — worth reviewing/removing on security grounds if unused.
- `author.php` and `home.php` are placeholders (empty).