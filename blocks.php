<?php

function get_theme_blocks() {
    $blocks = [
        
    ];
    return is_array($blocks) ? $blocks : [];
}


add_action('acf/init', 'theme_init_block_types');
function theme_init_block_types() {

    // Check function exists.
    if( function_exists('acf_register_block_type') ) {

        $blocks = get_theme_blocks();

        if(!empty($blocks)) {
            foreach($blocks as $block) {

                if(is_admin()) {
                    if(!file_exists(__DIR__ . '/components/blocks/'.$block.'.php')) {
                        touch(__DIR__ . '/components/blocks/'.$block.'.php'); 
                    }
                }

                $name = str_replace('_', ' ', $block);
                $title = ucwords($name);

                acf_register_block_type(array(
                    'name'              => $block,
                    'title'             => __($title),
                    'description'       => __('A custom for block ' . $name),
                    'render_template'   => __DIR__ . '/components/blocks/'.$block.'.php',
                    'category'          => 'theme-builder',
                    'keywords'          => [$name],
                    'mode'              => 'preview',
                    'align'             => 'full',
                    'supports'          => [
                        'align'  => ['wide', 'full'],
                        'mode'   => true,
                        'anchor' => true,
                    ],
                    'api_version'       => 3,
                    'acf_block_version' => 3,
                ));
            }
        }
    }
}

// Let blocks use the wide / full alignment options in the editor.
add_action( 'after_setup_theme', 'theme_block_editor_support' );
function theme_block_editor_support() {
    add_theme_support( 'align-wide' );
}

// Load the same front-end stylesheets inside the block editor so previews match the site.
add_action( 'enqueue_block_editor_assets', 'theme_block_editor_styles' );
function theme_block_editor_styles() {
    wp_enqueue_style( 'app-editor-fonts', get_template_directory_uri() . '/css/fonts.css', [], '1.0.1' );
    wp_enqueue_style( 'app-editor-theme', get_template_directory_uri() . '/css/main.css', ['app-editor-fonts'], '1.0.5' );
    wp_enqueue_style( 'app-editor-additional', get_template_directory_uri() . '/css/additional.css', ['app-editor-theme'], '1.0.6' );
}

// add_action('template_redirect', function() {
//     $block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
//     print_r($block_types);
//     exit;
// });

add_filter( 'allowed_block_types_all', function($allowed) {
    $blocks = get_theme_blocks();
    $allowed_blocks = array_map(function($b) {
        return 'acf/'.str_replace('_', '-', $b);
    }, $blocks);
    //$allowed_blocks[] = 'core/freeform';
    return $allowed_blocks;
}, 10);


add_action( 'block_categories_all', 'theme_init_block_category', 10, 2 );
function theme_init_block_category($categories) {
    return array_merge($categories,
        [
            [
                'slug'  => 'theme-builder',
                'title' => 'Theme Builder',
            ],
        ]
    );
}


function theme_image($id, $classes='column-image') {
    if(!$id) {
        return '';
    }
    $img = wp_get_attachment_image_src($id, 'full');
    return '<img src="'.$img[0].'" loading="lazy" sizes="'.wp_get_attachment_image_sizes($id, 'full').'" srcset="'.wp_get_attachment_image_srcset($id, 'full').'" alt="" class="'.$classes.'">';
}
