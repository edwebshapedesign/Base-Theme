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
                    'mode'              => 'edit',
                ));
            }
        }
    }
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
