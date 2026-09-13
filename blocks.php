<?php
/**
 * ACF block registration.
 *
 * Every folder inside components/blocks/ that contains a block.json is
 * registered automatically. Adding a block is therefore just:
 *
 *   components/blocks/<name>/
 *     block.json     Block metadata (name, title, assets, supports, acf settings)
 *     render.php     Render template (uses get_block_wrapper_attributes())
 *     style.css      Optional: loaded on front-end AND in the editor
 *     view.css       Optional: front-end only
 *     editor.css     Optional: editor only (never shipped to visitors)
 *     view.js        Optional: front-end only, deferred by core
 *
 * Assets are only enqueued on pages where the block is actually used, so
 * nothing block-specific should be added to the global stylesheets.
 */

/**
 * Absolute path to the blocks directory.
 */
function theme_blocks_dir() {
    return __DIR__ . '/components/blocks';
}

/**
 * Return the absolute paths of every block folder containing a block.json.
 *
 * @return string[]
 */
function theme_block_dirs() {
    static $dirs = null;

    if ( null === $dirs ) {
        $dirs  = [];
        $files = glob( theme_blocks_dir() . '/*/block.json' );

        if ( is_array( $files ) ) {
            foreach ( $files as $file ) {
                $dirs[] = dirname( $file );
            }
        }
    }

    return $dirs;
}

/**
 * Return the registered block names (e.g. "acf/testimonial") read from block.json.
 *
 * @return string[]
 */
function theme_block_names() {
    static $names = null;

    if ( null === $names ) {
        $names = [];

        foreach ( theme_block_dirs() as $dir ) {
            $meta = json_decode( file_get_contents( $dir . '/block.json' ), true );

            if ( is_array( $meta ) && ! empty( $meta['name'] ) ) {
                $names[] = $meta['name'];
            }
        }
    }

    return $names;
}

/**
 * Register each block from its block.json.
 *
 * ACF hooks into register_block_type() and reads the "acf" key from block.json,
 * so no acf_register_block_type() call is needed.
 */
add_action( 'init', 'theme_register_blocks', 5 );
function theme_register_blocks() {
    if ( ! function_exists( 'acf_register_block_type' ) ) {
        return;
    }

    foreach ( theme_block_dirs() as $dir ) {
        register_block_type( $dir );
    }
}

/**
 * Only allow the theme's own blocks in the editor. Core blocks are hidden by design.
 */
add_filter( 'allowed_block_types_all', 'theme_allowed_block_types', 10 );
function theme_allowed_block_types( $allowed ) {
    $names = theme_block_names();

    // If no blocks exist yet, fall back to whatever WordPress allows so the editor is still usable.
    if ( empty( $names ) ) {
        return $allowed;
    }

    // $names[] = 'core/freeform';
    return $names;
}

/**
 * Custom block categories. Set "category" in each block.json to one of these slugs.
 *
 *   theme-header  → "Header"  page/homepage headers and heroes
 *   theme-blocks  → "Blocks"  everything else
 *
 * They are prepended so they appear at the top of the inserter.
 */
add_filter( 'block_categories_all', 'theme_init_block_category', 10, 2 );
function theme_init_block_category( $categories ) {
    return array_merge(
        [
            [
                'slug'  => 'theme-header',
                'title' => 'Header',
                'icon'  => null,
            ],
            [
                'slug'  => 'theme-blocks',
                'title' => 'Blocks',
                'icon'  => null,
            ],
        ],
        $categories
    );
}

/**
 * ---------------------------------------------------------------------------
 * Shared "Block Settings" (acf-json/group_block_settings.json)
 *
 * A clone-source field group with:
 *   block_visible   True/false  Show the block on the front end (default on)
 *   padding_top     Select      none | small | medium | large | xlarge
 *   padding_bottom  Select      none | small | medium | large | xlarge
 *
 * Add it to a block: in the block's field group add a Clone field → choose
 * "Block Settings (clone source)", display Seamless. Then in render.php:
 *
 *   get_block_wrapper_attributes( [ 'class' => theme_block_settings_classes( 'my-block' ) ] )
 *
 * Visibility is enforced here for every acf/* block, so render.php does not
 * need to check it. In the editor a hidden block still renders, dimmed and
 * labelled (css/editor.css), so it can be edited and switched back on.
 * ---------------------------------------------------------------------------
 */

/**
 * Whether the current block should be shown to visitors.
 *
 * Reads the cloned block_visible field. Blocks that have not cloned the
 * settings group, or were saved before it was added, are visible.
 *
 * @param array|null $data Optional raw block data (from $block['data'] or
 *                         block attrs). When omitted get_field() is used.
 */
function theme_block_is_visible( $data = null ) {
    if ( is_array( $data ) ) {
        if ( array_key_exists( 'block_visible', $data ) ) {
            $value = $data['block_visible'];
        } elseif ( array_key_exists( 'field_block_settings_visible', $data ) ) {
            $value = $data['field_block_settings_visible'];
        } else {
            return true;
        }
    } else {
        $value = function_exists( 'get_field' ) ? get_field( 'block_visible' ) : null;
    }

    // Unset (null / '') means the toggle has never been saved → visible.
    if ( null === $value || '' === $value ) {
        return true;
    }

    return (bool) $value;
}

/**
 * Wrapper classes for a block that clones the Block Settings group.
 *
 * Returns e.g. "my-block has-pt-medium has-pb-large", plus
 * "is-hidden-on-frontend" inside the editor when the block is switched off.
 * Padding values outside the allowed set fall back to the defaults.
 * Utilities live in css/main.css.
 *
 * @param string|array $classes        Extra classes to include.
 * @param string       $default_top    Used when the field is empty/unset.
 * @param string       $default_bottom Used when the field is empty/unset.
 */
function theme_block_settings_classes( $classes = '', $default_top = 'medium', $default_bottom = 'medium' ) {
    $allowed = [ 'none', 'small', 'medium', 'large', 'xlarge' ];

    $top    = function_exists( 'get_field' ) ? get_field( 'padding_top' ) : '';
    $bottom = function_exists( 'get_field' ) ? get_field( 'padding_bottom' ) : '';

    $top    = in_array( $top, $allowed, true ) ? $top : $default_top;
    $bottom = in_array( $bottom, $allowed, true ) ? $bottom : $default_bottom;

    $classes   = is_array( $classes ) ? $classes : preg_split( '/\s+/', trim( (string) $classes ), -1, PREG_SPLIT_NO_EMPTY );
    $classes[] = 'has-pt-' . $top;
    $classes[] = 'has-pb-' . $bottom;

    if ( ! theme_block_is_visible() ) {
        $classes[] = 'is-hidden-on-frontend';
    }

    return implode( ' ', array_unique( $classes ) );
}

/**
 * Back-compat alias for the old helper name.
 */
function theme_block_spacing_classes( $classes = '', $default_top = 'medium', $default_bottom = 'medium' ) {
    return theme_block_settings_classes( $classes, $default_top, $default_bottom );
}

/**
 * Skip rendering acf/* blocks whose "Show block on the front end" toggle is off.
 *
 * Only applies to genuine front-end requests. The editor renders blocks via
 * ACF's own AJAX/REST endpoints, which never reach this filter, and the
 * is_admin/REST guards keep previews and the REST API untouched.
 */
add_filter( 'pre_render_block', 'theme_hide_invisible_blocks', 10, 2 );
function theme_hide_invisible_blocks( $pre_render, $parsed_block ) {
    if ( null !== $pre_render ) {
        return $pre_render;
    }

    $name = $parsed_block['blockName'] ?? '';
    if ( 0 !== strpos( $name, 'acf/' ) ) {
        return $pre_render;
    }

    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_preview() ) {
        return $pre_render;
    }

    $data = $parsed_block['attrs']['data'] ?? [];

    return theme_block_is_visible( $data ) ? $pre_render : '';
}

/**
 * Editor-only stylesheet (hidden-block badge etc.). Never loaded on the front end.
 */
add_action( 'enqueue_block_editor_assets', 'theme_editor_styles' );
function theme_editor_styles() {
    $relative = 'css/editor.css';
    $file     = get_template_directory() . '/' . $relative;

    if ( ! file_exists( $file ) ) {
        return;
    }

    wp_enqueue_style(
        'app-editor',
        get_template_directory_uri() . '/' . $relative,
        [],
        (string) filemtime( $file )
    );
}

/**
 * Responsive <img> from an attachment ID.
 */
function theme_image( $id, $classes = 'column-image' ) {
    if ( ! $id ) {
        return '';
    }

    $img = wp_get_attachment_image_src( $id, 'full' );

    if ( ! $img ) {
        return '';
    }

    $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );

    return '<img src="' . esc_url( $img[0] ) . '" loading="lazy" sizes="' . esc_attr( wp_get_attachment_image_sizes( $id, 'full' ) ) . '" srcset="' . esc_attr( wp_get_attachment_image_srcset( $id, 'full' ) ) . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( $classes ) . '">';
}
