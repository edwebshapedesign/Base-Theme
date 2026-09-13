# Creating a block, step by step

This guide walks through adding a new ACF block to the theme. Follow it in order. The worked example is a cut-down **Text Block** (`acf/text-block`) with a title, some text and the shared Block Settings. Swap the names for your own block.

If you get stuck, compare your files with `components/blocks/text_block/`. It is the real Text Block that ships with the theme. It has more fields and layouts than the example below, but it follows every rule in this guide.

---

## Before you start

- You need the site running locally with **ACF Pro** active. Nothing else is required.
- Blocks are registered automatically. `blocks.php` finds every folder in `components/blocks/` that contains a `block.json`. You never edit `blocks.php` or `functions.php` to add a block. If you think you need to change `functions.php`, stop and check with John or Ed first.
- Pick two names now and stick to them:

| What | Example | Rule |
|---|---|---|
| Folder name | `text_block` | lowercase, underscores |
| Block name | `acf/text-block` | `acf/` prefix, lowercase, hyphens |
| Field group title | `Block: Text Block` | always starts with `Block: ` |

---

## Step 1: Create the folder

Create `components/blocks/text_block/` with these files. Only `block.json` and `render.php` are required.

```
components/blocks/text_block/
├── block.json     required. Name, title, category, ACF settings, which CSS to load
├── render.php     required. The HTML output
├── style.css      loaded on the front end AND in the editor
├── editor.css     editor only, never sent to visitors
├── view.css       optional. Front end only
├── view.js        optional. Front end only
└── preview.png    optional. Screenshot shown in the block inserter
```

Do not put a `functions.php` or any other PHP file in the folder.

---

## Step 2: Write `block.json`

Copy this and change the values marked with a comment. JSON does not allow comments, so remove them.

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "acf/text-block",
  "version": "1.0.0",
  "title": "Text Block",
  "description": "A title and a column of text.",
  "category": "theme-blocks",
  "icon": "text",
  "keywords": ["text", "content", "copy"],
  "textdomain": "base-theme",
  "acf": {
    "mode": "preview",
    "renderTemplate": "render.php",
    "blockVersion": 3
  },
  "style": "file:./style.css",
  "editorStyle": "file:./editor.css",
  "supports": {
    "anchor": true,
    "align": ["wide", "full"],
    "jsx": true
  },
  "example": {
    "attributes": {
      "mode": "preview",
      "data": {
        "preview_image_help": "preview.png"
      }
    }
  }
}
```

What each part means:

- **name**: must match the ACF field group's location rule in Step 3. This is the single most common cause of a "block does nothing" bug.
- **version**: the cache-busting string for this block's CSS and JS. Bump it every time you change `style.css`, `editor.css`, `view.css` or `view.js`, otherwise visitors keep the old file.
- **category**: `theme-blocks` for normal blocks. Use `theme-header` only for heroes and page headers.
- **icon**: any WordPress Dashicon name, without the `dashicons-` prefix. Browse them at developer.wordpress.org/resource/dashicons.
- **acf.mode = preview** and **blockVersion = 3**: editors see a live rendered preview instead of a form. Always keep these.
- **style** and **editorStyle**: only list files that exist. A missing file causes a broken stylesheet link.
- **supports.align**: the alignments editors may pick. Leave out `"align"` completely if the block should never be wide or full.
- **supports.multiple: false**: add this only for blocks that should appear once per page, such as a page header.
- **example**: shows `preview.png` when the block is hovered in the inserter. Step 6 explains the matching PHP.

---

## Step 3: Create the ACF field group

Do this in the WordPress admin. ACF saves the result to `acf-json/` automatically, so the fields travel with the theme in git.

1. Go to **ACF → Field Groups → Add New**.
2. Title: `Block: Text Block`.
3. Add your content fields. For the example:

| Label | Field name | Type | Notes |
|---|---|---|---|
| Title | `title` | Text | |
| Text | `text` | WYSIWYG Editor | Toolbar: Basic. Media upload: No |

4. Add the shared **Block Settings**. This gives the block the padding controls and the "Show block on the front end" toggle.
   - Add a field, type **Clone**.
   - Label: `Block settings`. Field name: `block_settings`.
   - Fields: choose **Block Settings (clone source)**.
   - Display: **Seamless**. Layout: **Block**.
   - Leave "Prefix field labels" and "Prefix field names" off.
5. In **Settings → Location**, set the rule to **Block** → **is equal to** → **Text Block**. If your block is not in the list, `block.json` has not been picked up yet. Check the folder name and JSON for typos, then reload.
6. Set **Settings → Presentation → Position** to `Normal`. Leave everything else at the default.
7. Click **Save Changes**.

Now confirm the JSON was written. You should see a new file in `acf-json/` whose filename is the group key, for example `acf-json/group_68bd1a2c3d4e5.json`. Open it and check `"value": "acf/text-block"` appears in the location rule. If the file is missing, the `acf-json` folder is not writable. Ask for help rather than continuing.

**Write down your field names.** The names you typed in the "Field name" boxes are exactly what `render.php` will read with `get_field()`. If the names in the field group and the template ever drift apart, the block silently renders nothing. That is what broke the Home Hero in September 2026.

Optional, for anyone comfortable with JSON: you can instead copy `acf-json/group_block_text_block.json`, rename it, change every `key`, `title`, `name` and the location `value`, and skip the admin UI. After doing that you must go to **ACF → Field Groups** and click **Sync** so the database matches.

---

## Step 4: Write `render.php`

This is the template. ACF gives you four variables: `$block`, `$content`, `$is_preview` and `$post_id`. Copy this and adjust the field names to match Step 3 exactly.

```php
<?php
/**
 * Text Block.
 *
 * Fields (see acf-json): title, text
 * Plus the cloned Block Settings: block_visible, padding_top, padding_bottom
 */

// 1. Inserter preview. When the block is hovered in the inserter, ACF passes the
//    "example" data from block.json. Show the screenshot instead of a real render.
if ( ! empty( $block['data']['preview_image_help'] ) ) {
    $preview_file = basename( $block['data']['preview_image_help'] );

    if ( file_exists( __DIR__ . '/' . $preview_file ) ) {
        $preview_url = get_template_directory_uri() . '/components/blocks/text_block/' . $preview_file;
        echo '<img src="' . esc_url( $preview_url ) . '" alt="' . esc_attr__( 'Text Block preview', 'base-theme' ) . '" style="width:100%;height:auto;display:block;">';
        return;
    }
}

// 2. Read the fields. One variable per field, named after the field.
$title = get_field( 'title' );
$text  = get_field( 'text' );

// 3. Editor placeholder, so a brand-new empty block is still visible while editing.
if ( $is_preview && ! $title && ! $text ) {
    $title = __( 'Text Block', 'base-theme' );
    $text  = '<p>' . __( 'Add a title and some text in the block settings panel.', 'base-theme' ) . '</p>';
}

// 4. Nothing to show on the front end? Output nothing.
if ( ! $title && ! $text ) {
    return;
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => theme_block_settings_classes( 'text-block' ) ] ); ?>>
    <div class="container">

        <?php if ( $title ) : ?>
            <h2 class="text-block__title" data-animate><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $text ) : ?>
            <div class="text-block__body" data-animate>
                <?php echo wp_kses_post( $text ); ?>
            </div>
        <?php endif; ?>

    </div>
</div>
```

Rules for the template:

- **The outer element always uses `get_block_wrapper_attributes()`.** It adds the alignment, anchor and custom class names that editors set. Never hand-write the outer `class=""`.
- **Pass your block's CSS class through `theme_block_settings_classes()`.** It appends the padding classes from Block Settings and, in the editor, marks the block if it is switched off. Your class name should match the folder name but with hyphens, so folder `text_block` uses class `text-block`.
- **Do not check the visibility toggle yourself.** `blocks.php` hides switched-off blocks on the front end for every block automatically.
- **Escape everything you output.** Plain text: `esc_html()`. Attributes: `esc_attr()`. URLs: `esc_url()`. WYSIWYG content: `wp_kses_post()`. Never echo a field raw.
- **Images**: fields set to return an ID. Output them with `wp_get_attachment_image( $id, 'large' )` or the theme helper `theme_image( $id, 'my-class' )`.
- **Buttons**: if you add a `buttons` repeater with sub fields `button_text`, `button_link`, `button_style` and `button_arrow`, output it with `get_template_part( 'components/buttons' )` inside a `<div class="button-holder">`. See `components/blocks/text_block/render.php`.
- **Animations**: add `data-animate` to elements that should fade in on scroll. Do not write your own JavaScript for this.
- **Comment your field list at the top** so the next person can see what the template expects without opening the JSON.

---

## Step 5: Write the CSS

`style.css` loads on the front end and in the editor. Keep it scoped to your block's class.

```css
/* Text Block: shared styles (front end + editor) */

.text-block {
    /* Vertical padding comes from Block Settings (.has-pt-* / .has-pb-*). Do not set it here. */
    background: var(--color-white, #fff);
    color: var(--color-dark, #111827);
}

.text-block__title {
    margin: 0 0 var(--space-md, 1.5rem);
}

.text-block__body {
    max-width: 65ch;
}
```

`editor.css` is for editor-only tweaks, usually an outline so an empty block is easy to find on the canvas.

```css
/* Text Block: editor only styles (never shipped to visitors) */

.wp-block-acf-text-block .text-block {
    outline: 1px dashed rgba(0, 0, 0, 0.2);
    outline-offset: -1px;
}
```

Rules:

- **Never add block styles to `css/main.css`** or any other global stylesheet. Block CSS only loads on pages that use the block, and putting it in the global file defeats that.
- **Never set `padding-top` or `padding-bottom` on the wrapper.** Block Settings handles it.
- **Use the design tokens** from `css/main.css` with a fallback: `var(--color-dark, #111827)`. Do not hard-code brand colours.
- Class names use the block name as a prefix: `.text-block`, `.text-block__title`.
- Editor classes are prefixed with `.wp-block-acf-<block-name>`, which WordPress adds automatically.

---

## Step 6: Add the inserter screenshot

1. Build the block once on a test page with realistic content.
2. Screenshot just the block at roughly 1200 by 700 pixels.
3. Save it as `preview.png` inside your block folder. Keep it under about 500 KB.

The `example` in `block.json` and the check at the top of `render.php` already wire it up. If you skip this step the inserter shows a live render of the empty state instead, which is fine.

---

## Step 7: Test it

Work through every line. Do not skip the last two.

- [ ] Open a page in the editor. Your block appears in the inserter under **Blocks** (or **Header**) with the right title and icon.
- [ ] Insert it. The empty placeholder from Step 4 shows.
- [ ] Fill in each field in the sidebar. The preview updates live.
- [ ] Set padding top and bottom to different values. The spacing changes.
- [ ] Turn off **Show block on the front end**. The block dims and shows a red "Hidden on the front end" badge in the editor.
- [ ] Publish and view the page. The block is missing from the front end. Turn the toggle back on, update, and it reappears.
- [ ] Try the wide and full alignments if you allowed them.
- [ ] View the page source. The block's `style.css` is loaded with `?ver=1.0.0` on the URL and `editor.css` is not loaded.
- [ ] Check on a phone-sized viewport.
- [ ] Enable **WP_DEBUG** locally for one page load and confirm there are no PHP notices from your template.

---

## Step 8: Commit

Commit these together in one commit so the block is never half-added:

```
components/blocks/text_block/    (all files)
acf-json/group_xxxxxxxx.json     (the new field group)
```

Commit message pattern: `Add Text Block`. Do not commit test pages or media.

Anyone who pulls your branch must go to **ACF → Field Groups** and click **Sync** on the new group once. Mention it in the pull request.

---

## Common mistakes and what they look like

| Symptom | Cause | Fix |
|---|---|---|
| Block is not in the inserter | `block.json` is invalid JSON, or the folder has no `block.json` | Paste the JSON into a validator. Check the filename is exactly `block.json`. |
| Block inserts but the sidebar has no fields | Field group location rule points at the wrong block name | Compare `"name"` in `block.json` with the location rule in ACF. |
| Block renders nothing, editor shows only the placeholder | Field names in `render.php` do not match the field group | Read the field names from the JSON in `acf-json/` and fix `get_field()` calls. |
| Styles do not update after a change | `version` in `block.json` not bumped | Bump it, hard refresh. |
| Padding options do nothing | Wrapper class not built with `theme_block_settings_classes()` | Use it in `get_block_wrapper_attributes()`. |
| Padding looks doubled | Block CSS sets its own vertical padding | Remove `padding-top` / `padding-bottom` from the wrapper rule. |
| Block appears on the front end when switched off | Page cache | Clear the cache. The filter in `blocks.php` runs on every uncached render. |
| Alignment or anchor settings ignored | Outer element does not use `get_block_wrapper_attributes()` | Use it for the outer tag. |

---

## Reference

- `components/blocks/text_block/` is the worked example. It shows a WYSIWYG field, two repeaters (checklist and buttons), the shared buttons partial, an image field with conditional logic, a layout select and a background choice.
- `blocks.php` documents the helpers: `theme_block_settings_classes()`, `theme_block_is_visible()`, `theme_image()`.
- `README.md`, section "ACF Blocks", covers the same ground in less detail.
