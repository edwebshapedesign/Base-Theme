<?php

include('blocks.php');
add_action( 'after_setup_theme', 'qsd_setup' );
function qsd_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form' ) );
    register_nav_menus( array( 'main-menu' => 'Main Menu') );
}

add_action( 'wp_enqueue_scripts', 'qsd_load_scripts' );
function qsd_load_scripts() {
    wp_enqueue_style( 'app-style', get_stylesheet_uri() );
    wp_enqueue_style( 'app-fonts', get_template_directory_uri() . '/css/fonts.css', ['app-theme'], '1.0.1' );
    wp_enqueue_style( 'app-theme', get_template_directory_uri() . '/css/main.css?v=1.0.5', ['app-style'] );
    wp_enqueue_style( 'app-additional', get_template_directory_uri() . '/css/additional.css?v=1.0.6', ['app-theme'] );
    wp_enqueue_style( 'app-animations', get_template_directory_uri() . '/css/entrance-animations.css?v=1.0.2', ['app-theme'] );

    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-3.5.1.min.js', [], null, true);
    wp_enqueue_script( 'app-script', get_template_directory_uri() . '/js/main.js?v=1.0.1', ['jquery'], null, true );
    wp_enqueue_script( 'app-video', get_template_directory_uri() . '/js/hls.min.js?v=1.0.1', ['jquery'], null, true );
    wp_enqueue_script('app-additional', get_template_directory_uri() . '/js/additional.js?v=1.0', ['jquery', 'app-script'], null, true);
    wp_enqueue_script('app-animations', get_template_directory_uri() . '/js/entrance-animations.js?v=1.0.2', ['jquery', 'app-script'], null, true);
    //wp_enqueue_script( 'jquery' );
    
}


// function enqueue_acf_inline_editor_support() {
//     if (is_admin()) {
//         wp_enqueue_script(
//             'acf-inline-editor',
//             get_template_directory_uri() . '/js/acf-inline-editor.js',
//             ['acf-input'],
//             false,
//             true
//         );
//     }
// }
// add_action('enqueue_block_editor_assets', 'enqueue_acf_inline_editor_support');

// add_action( 'admin_enqueue_scripts', 'qsd_load_admin_css');
// function qsd_load_admin_css() {
//     wp_enqueue_style( 'app-theme', get_template_directory_uri() . '/css/fortress-fire.css');
// }

add_filter( 'the_content_more_link', 'qsd_read_more_link' );
function qsd_read_more_link() {
        if ( ! is_admin() ) {
        return ' <a href="' . esc_url( get_permalink() ) . '" class="more-link">...</a>';
    }
}

add_filter( 'excerpt_more', 'qsd_excerpt_read_more_link' );
function qsd_excerpt_read_more_link( $more ) {
    if ( ! is_admin() ) {
        global $post;
        return '...';
        // return ' <a href="' . esc_url( get_permalink( $post->ID ) ) . '" class="more-link">...</a>';
    }
	
}



add_filter('xmlrpc_enabled', '__return_false');
//add_filter('use_block_editor_for_post', '__return_false', 10);

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Site Content',
		'menu_title'	=> 'Site Content',
		'menu_slug' 	=> 'site-content',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}

add_filter( 'wp_lazy_loading_enabled', '__return_true' );


remove_filter ('acf_the_content', 'wpautop');

add_filter('wpcf7_autop_or_not', '__return_false' );

add_filter('img_caption_shortcode', 'no_caption', 10, 3);

function no_caption($deprecated, $attr, $content) { return $content; };


add_image_size( 'top-content', 1080, 720, array( 'center', 'center' ) );

add_filter( 'image_size_names_choose', 'top_content_size' );
function top_content_size( $sizes ) {
    return array_merge( $sizes, array(
        'top-content' => __( 'Top Content' ),
    ) );
}

add_image_size( 'gallery-thumbnail', 149, 438, array( 'center', 'center' ) );

add_action( 'wp_enqueue_scripts', 'bs_dequeue_dashicons' );

function bs_dequeue_dashicons() {
    if ( ! is_user_logged_in() ) {
        wp_deregister_style( 'dashicons' );
    }
}


// Add "nocookie" To WordPress oEmbeded Youtube Videos
function ev_youtube_nocookie_oembed( $html ) {
	return str_replace( 'youtube.com', 'youtube-nocookie.com', $html );
}
add_filter( 'embed_oembed_html', 'ev_youtube_nocookie_oembed' ); // WordPress


function numeric_posts_nav() {
 
    if( is_singular() ) {
        return;
    }

    global $wp_query;

    if( $wp_query->max_num_pages <= 1 ) {
        return;
    }

    $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
    $max   = intval( $wp_query->max_num_pages );

    if ( $paged >= 1 )
        $links[] = $paged;

    if ( $paged >= 3 ) {
        $links[] = $paged - 1;
        $links[] = $paged - 2;
    }

    if ( ( $paged + 2 ) <= $max ) {
        $links[] = $paged + 2;
        $links[] = $paged + 1;
    }

    echo '<div class="page-selector-holder">' . "\n";

    // if ( get_previous_posts_link() )
    //     printf( '%s' . "\n", get_previous_posts_link() );

    if ( ! in_array( 1, $links ) ) {
        $class = 1 == $paged ? ' class="active"' : '';

        printf( '<a class="page-selector-link w-inline-block" href="%s">%s</a>' . "\n", esc_url( get_pagenum_link( 1 ) ), '1' );

        if ( ! in_array( 2, $links ) )
            echo '<li>…</li>';
    }

    sort( $links );
    foreach ( (array) $links as $link ) {
        $class = $paged == $link ? ' class="active"' : '';
        printf( '<a class="page-selector-link w-inline-block" href="%s">%s</a>' . "\n", esc_url( get_pagenum_link( $link ) ), $link );
    }

    if ( ! in_array( $max, $links ) ) {
        if ( ! in_array( $max - 1, $links ) )
            echo '<div>…</div>' . "\n";

        $class = $paged == $max ? ' class="active"' : '';
        printf( '<a class="page-selector-link w-inline-block" href="%s">%s</a>' . "\n", esc_url( get_pagenum_link( $max ) ), $max );
    }

    // if ( get_next_posts_link() )
    //     printf( '%s' . "\n", get_next_posts_link() );

    echo '</div>' . "\n";
 
}



function buildTree( array &$elements, $parentId = 0 ) {
    $branch = array();
    foreach ( $elements as &$element )
    {
        if ( $element->menu_item_parent == $parentId )
        {
            $children = buildTree( $elements, $element->ID );
            if ( $children ) {
                $element->menu_children = $children;
            } else {
                $element->menu_children = false;
            }


            $branch[$element->ID] = $element;
            unset( $element );
        }
    }
    return $branch;
}

function nav_to_array($menu_id) {
    $items = wp_get_nav_menu_items( $menu_id );
    return  $items ? buildTree( $items, 0 ) : false;
}


add_shortcode( 'pageurl', 'sc_pageurl' );

function sc_pageurl() {

    global $wp;
    return home_url( $wp->request );

}


function insertImageToPost( $url, $parent_post_id = null ) {

	if ( ! class_exists( 'WP_Http' ) ) {
		require_once ABSPATH . WPINC . '/class-http.php';
	}

	$http     = new WP_Http();
	$response = $http->request( $url );
	if ( 200 !== $response['response']['code'] ) {
		return false;
	}

	$upload = wp_upload_bits( basename( $url ), null, $response['body'] );
	if ( ! empty( $upload['error'] ) ) {
		return false;
	}

	$file_path        = $upload['file'];
	$file_name        = basename( $file_path );
	$file_type        = wp_check_filetype( $file_name, null );
	$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
	$wp_upload_dir    = wp_upload_dir();

	$post_info = array(
		'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
		'post_mime_type' => $file_type['type'],
		'post_title'     => $attachment_title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// Create the attachment.
	$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

	// Include image.php.
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Generate the attachment metadata.
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

	// Assign metadata to attachment.
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;

}



function force_blog_prefix_in_post_links($permalink, $post) {
    if ($post->post_type === 'post') {
        return home_url('/blog/' . $post->post_name . '/');
    }
    return $permalink;
}
add_filter('post_link', 'force_blog_prefix_in_post_links', 10, 2);

function modify_permalink_output($url, $post) {
    if ($post->post_type === 'post') {
        return home_url('/blog/' . $post->post_name . '/');
    }
    return $url;
}
add_filter('post_type_link', 'modify_permalink_output', 10, 2);

function add_blog_rewrite_rule() {
    add_rewrite_rule(
        '^blog/([^/]+)/?$',
        'index.php?name=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^blog/page/([0-9]+)/?$',
        'index.php?paged=$matches[1]',
        'top'
    );
}
add_action('init', 'add_blog_rewrite_rule');

function flush_rewrite_rules_on_activation() {
    add_blog_rewrite_rule();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'flush_rewrite_rules_on_activation');

function flush_rewrite_rules_on_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'flush_rewrite_rules_on_deactivation');

function update_yoast_canonical_url($canonical) {
    if (is_singular('post')) {
        global $post;
        return home_url('/blog/' . $post->post_name . '/');
    }
    return $canonical;
}
add_filter('wpseo_canonical', 'update_yoast_canonical_url');





