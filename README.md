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

The theme registers its own ACF options page (**Site Content**), so no manual `acf_add_options_page()` call is needed.

## Structure

```
base-theme/
├── style.css              Theme header + design tokens / reset / .container
├── functions.php          Setup, asset enqueue, ACF options, rewrites, helpers
├── blocks.php             ACF block registration + block category + theme_image() (included by functions.php)
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
├── components/
│   ├── backgrounds.php    Background variants (full / gradient / dot patterns)
│   ├── buttons.php        ACF buttons repeater → styled <a class="btn …">
│   └── blocks/
│       ├── parts/nav.php  Primary navigation template part (ACF options-driven)
│       └── *.php          One render template per registered block (auto-created)
│
├── css/
│   ├── normalize.css      Reset (enqueued first)*
│   ├── components.css     Base component styles*
│   ├── main.css           Design tokens, reset, .container
│   ├── fonts.css          @font-face — Montserrat + Source Sans 3 (local)
│   ├── additional.css     Component tweaks / responsive overrides
│   ├── case-study.css     Case-study page styles*
│   └── entrance-animations.css
│
├── js/
│   ├── main.js            App entry
│   ├── additional.js      jQuery-scoped entry
│   ├── entrance-animations.js
│   ├── acf-inline-editor.js  Admin-only ACF inline editor support*
│   ├── jquery-3.5.1.min.js   Bundled jQuery (replaces core jQuery)
│   └── hls.min.js         HLS.js — for streamed/adaptive video
│
├── fonts/                 Local font files (Montserrat, Source Sans 3) — see fonts.css
├── images/                Theme images (favicon, webclip, icons, …)
└── videos/                Video assets

* referenced in functions.php but not present in this checkout — see Notes.
```

## How it works

### Theme setup (`functions.php`)

On `after_setup_theme` the theme enables `title-tag`, `automatic-feed-links`, `post-thumbnails` and HTML5 `search-form`, and registers a single **Main Menu** nav location (`main-menu`).

`functions.php` also:

- **Includes `blocks.php`** — this is what activates all ACF block registration (see below).
- **Registers the ACF options page** (`Site Content`, slug `site-content`) that header, footer and archive templates read from via `get_field( '…', 'option' )`.
- Enqueues all styles and scripts (order and cache-busting query strings matter here — `main.css` depends on `components.css`, `additional.css` on `main.css`, etc.).
- **Replaces core jQuery** with the bundled `js/jquery-3.5.1.min.js`, loaded in the footer, and enqueues `main.js`, `hls.min.js`, `additional.js` and `entrance-animations.js` on top of it.
- Loads `acf-inline-editor.js` in the block editor only.

### ACF Blocks

`blocks.php` drives page building. `get_theme_blocks()` returns a flat list of block slugs (`hero`, `page_header`, `about_us`, `stats`, `services`, `testimonials`, `video`, `faqs`, `cta`, `blog_editor`, and so on). On `acf/init` each slug is registered with `acf_register_block_type()`, pointing its render template at `components/blocks/{slug}.php` under a custom **Theme Builder** block category.

Two conveniences:

- If a block's render template doesn't exist yet, `blocks.php` will `touch()` an empty file for it in the admin, so adding a block is: add the slug to the array, fill in the template.
- `allowed_block_types_all` is filtered so **only** these theme blocks appear in the editor — core blocks are hidden by design.

Pages are assembled by dropping blocks into the editor; `page.php` calls `the_content()` and each block renders itself (including any JSON-LD schema it outputs). `theme_image( $id, $classes )` is a shared helper returning a responsive `<img>` with `srcset`/`sizes` from an attachment ID.

### Header, footer & navigation

`header.php` and `footer.php` are almost entirely ACF-options-driven — script slots, logos, footer columns and links, social icons and awards all come from option fields. Social icons render as CSS-masked SVGs (per-icon `mask-image` injected inline). `header.php` outputs an `Organization` JSON-LD schema block, optionally extended with a `memberOf` value via a theme global. Navigation lives in `components/blocks/parts/nav.php` with a lightweight active-link helper.

### Templates

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

- **Missing assets in this checkout:** `functions.php` enqueues `css/normalize.css`, `css/components.css`, `css/case-study.css` and `js/acf-inline-editor.js`, and `fonts/` / `images/` / `videos/` are empty. Add these before deploying, or the enqueue calls will 404.
- Markup carries Webflow artefacts (`w-container`, `wf-section`, `data-wf-*`) — harmless, can be cleaned up over time.
- `header.php` contains a global `whenClicked`-attribute `eval()` handler inherited from the export — worth reviewing/removing on security grounds if unused.
- `author.php` and `home.php` are placeholders (empty).