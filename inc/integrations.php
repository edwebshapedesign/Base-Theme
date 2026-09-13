<?php
/**
 * Integrations — tracking and structured data driven by Site Content → Scripts.
 *
 * One small function per integration, all reading ACF option fields defined in
 * acf-json/group_site_scripts.json. Output order in <head>:
 *
 *   wp_head 1  Custom header scripts   (consent managers go here, before GTM)
 *   wp_head 2  Google Tag Manager
 *   wp_head 3  Umami analytics
 *   wp_head 5  Organization + WebSite JSON-LD schema
 *
 * Body-open and footer slots carry the GTM <noscript> and custom snippets.
 *
 * To add an integration: add its fields to the field group (own tab), write a
 * theme_<name>_output() function here, hook it, and document it in the README.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// Shared guards
// ---------------------------------------------------------------------------

/**
 * Option field helper that is safe if ACF is inactive.
 */
function theme_option( $field, $default = '' ) {
    if ( ! function_exists( 'get_field' ) ) {
        return $default;
    }
    $value = get_field( $field, 'option' );
    return ( null === $value || '' === $value ) ? $default : $value;
}

/**
 * Never output third-party snippets in the editor, on previews or in the Customizer.
 */
function theme_scripts_allowed() {
    return ! ( is_admin() || is_preview() || is_customize_preview() );
}

/**
 * Tracking (GTM, Umami) additionally respects the "exclude logged-in users" toggle.
 */
function theme_tracking_allowed() {
    if ( ! theme_scripts_allowed() ) {
        return false;
    }
    if ( theme_option( 'tracking_exclude_logged_in' ) && is_user_logged_in() ) {
        return false;
    }
    return true;
}

// ---------------------------------------------------------------------------
// Custom scripts (raw textareas: header_scripts, body_scripts, footer_scripts)
// ---------------------------------------------------------------------------

/**
 * Output one raw snippet field. Deliberately unescaped — the options page is
 * restricted to manage_options for exactly this reason. Also accepts the old
 * repeater shape ([ ['script' => '...'], ... ]) from earlier theme versions.
 */
function theme_output_option_scripts( $field ) {
    if ( ! theme_scripts_allowed() ) {
        return;
    }

    $value = theme_option( $field );
    if ( empty( $value ) ) {
        return;
    }

    if ( is_array( $value ) ) {
        $value = implode( "\n", array_filter( array_map( fn( $row ) => $row['script'] ?? '', $value ) ) );
    }

    $value = trim( (string) $value );
    if ( '' !== $value ) {
        echo "\n<!-- Site Scripts: " . esc_html( $field ) . " -->\n" . $value . "\n";
    }
}

add_action( 'wp_head', 'theme_output_header_scripts', 1 );
function theme_output_header_scripts() {
    theme_output_option_scripts( 'header_scripts' );
}

add_action( 'wp_body_open', 'theme_output_body_scripts', 1 );
function theme_output_body_scripts() {
    theme_output_option_scripts( 'body_scripts' );
}

add_action( 'wp_footer', 'theme_output_footer_scripts', 1 );
function theme_output_footer_scripts() {
    theme_output_option_scripts( 'footer_scripts' );
}

// ---------------------------------------------------------------------------
// Google Tag Manager (gtm_enabled, gtm_id)
// ---------------------------------------------------------------------------

/**
 * Normalise a container ID: uppercase, no "GTM-" prefix. Returns '' if invalid.
 */
function theme_gtm_normalise_id( $value ) {
    $value = strtoupper( trim( (string) $value ) );
    $value = preg_replace( '/^GTM-/', '', $value );
    return preg_match( '/^[A-Z0-9]{4,12}$/', $value ) ? $value : '';
}

/**
 * Full container ID (e.g. "GTM-ABC1234") if GTM is enabled and valid, else ''.
 */
function theme_gtm_id() {
    if ( ! theme_option( 'gtm_enabled' ) ) {
        return '';
    }
    $id = theme_gtm_normalise_id( theme_option( 'gtm_id' ) );
    return $id ? 'GTM-' . $id : '';
}

add_action( 'wp_head', 'theme_gtm_head', 2 );
function theme_gtm_head() {
    $id = theme_gtm_id();
    if ( ! $id || ! theme_tracking_allowed() ) {
        return;
    }
    $id = esc_js( $id );
    echo <<<HTML

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$id}');</script>
<!-- End Google Tag Manager -->

HTML;
}

add_action( 'wp_body_open', 'theme_gtm_body', 2 );
function theme_gtm_body() {
    $id = theme_gtm_id();
    if ( ! $id || ! theme_tracking_allowed() ) {
        return;
    }
    $src = esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $id );
    echo "\n<!-- Google Tag Manager (noscript) -->\n"
        . '<noscript><iframe src="' . $src . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>'
        . "\n<!-- End Google Tag Manager (noscript) -->\n";
}

// Validate + normalise the ID on save.
add_filter( 'acf/validate_value/name=gtm_id', 'theme_gtm_validate_id', 10, 4 );
function theme_gtm_validate_id( $valid, $value, $field, $input_name ) {
    if ( true !== $valid || '' === trim( (string) $value ) ) {
        return $valid;
    }
    return theme_gtm_normalise_id( $value )
        ? true
        : __( 'Enter a Google Tag Manager container ID such as GTM-ABC1234 (4–12 letters or numbers, with or without the GTM- prefix).', 'base-theme' );
}

add_filter( 'acf/update_value/name=gtm_id', 'theme_gtm_save_id' );
function theme_gtm_save_id( $value ) {
    return theme_gtm_normalise_id( $value ) ?: $value;
}

// ---------------------------------------------------------------------------
// Umami analytics (umami_enabled, umami_script_url, umami_website_id, umami_domains)
// ---------------------------------------------------------------------------

add_action( 'wp_head', 'theme_umami_head', 3 );
function theme_umami_head() {
    if ( ! theme_option( 'umami_enabled' ) || ! theme_tracking_allowed() ) {
        return;
    }

    $website_id = trim( (string) theme_option( 'umami_website_id' ) );
    $script_url = trim( (string) theme_option( 'umami_script_url', 'https://cloud.umami.is/script.js' ) );
    $domains    = trim( (string) theme_option( 'umami_domains' ) );

    if ( ! $website_id || ! $script_url ) {
        return;
    }

    $attrs = ' data-website-id="' . esc_attr( $website_id ) . '"';
    if ( $domains ) {
        $attrs .= ' data-domains="' . esc_attr( preg_replace( '/\s+/', '', $domains ) ) . '"';
    }

    echo "\n<!-- Umami -->\n<script defer src=\"" . esc_url( $script_url ) . '"' . $attrs . "></script>\n";
}

add_filter( 'acf/validate_value/name=umami_website_id', 'theme_umami_validate_id', 10, 4 );
function theme_umami_validate_id( $valid, $value, $field, $input_name ) {
    if ( true !== $valid || '' === trim( (string) $value ) ) {
        return $valid;
    }
    return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim( $value ) )
        ? true
        : __( 'Enter the Umami website ID (a UUID like 9f1c2a3b-4d5e-6f70-8a9b-0c1d2e3f4a5b).', 'base-theme' );
}

add_filter( 'acf/validate_value/name=umami_script_url', 'theme_umami_validate_url', 10, 4 );
function theme_umami_validate_url( $valid, $value, $field, $input_name ) {
    if ( true !== $valid || '' === trim( (string) $value ) ) {
        return $valid;
    }
    return ( 0 === strpos( $value, 'https://' ) && filter_var( $value, FILTER_VALIDATE_URL ) )
        ? true
        : __( 'Enter the full https:// URL of the Umami tracking script.', 'base-theme' );
}

// ---------------------------------------------------------------------------
// Organization + WebSite schema (schema_enabled, schema_org_type, schema_org_name,
// schema_org_logo, schema_phone, schema_email, schema_same_as, schema_search)
// ---------------------------------------------------------------------------

/**
 * Build the JSON-LD graph. Filter `theme_schema_graph` to extend it.
 */
function theme_schema_graph() {
    $home     = home_url( '/' );
    $org_id   = $home . '#organization';
    $site_id  = $home . '#website';
    $name     = theme_option( 'schema_org_name', get_bloginfo( 'name' ) );
    $type     = theme_option( 'schema_org_type', 'Organization' );
    $logo_id  = (int) theme_option( 'schema_org_logo' ) ?: (int) theme_option( 'site_logo' );
    $phone    = trim( (string) theme_option( 'schema_phone' ) );
    $email    = trim( (string) theme_option( 'schema_email' ) );
    $same_as  = array_values( array_filter( array_map( fn( $r ) => esc_url_raw( $r['url'] ?? '' ), (array) theme_option( 'schema_same_as', [] ) ) ) );

    $organization = [
        '@type' => in_array( $type, [ 'Organization', 'LocalBusiness', 'Corporation', 'ProfessionalService' ], true ) ? $type : 'Organization',
        '@id'   => $org_id,
        'name'  => $name,
        'url'   => $home,
    ];

    if ( $logo_id && ( $logo = wp_get_attachment_image_src( $logo_id, 'full' ) ) ) {
        $organization['logo'] = [
            '@type'  => 'ImageObject',
            'url'    => $logo[0],
            'width'  => $logo[1],
            'height' => $logo[2],
        ];
    }
    if ( $phone ) {
        $organization['telephone'] = $phone;
    }
    if ( $email ) {
        $organization['email'] = $email;
    }
    if ( $same_as ) {
        $organization['sameAs'] = $same_as;
    }

    $website = [
        '@type'     => 'WebSite',
        '@id'       => $site_id,
        'name'      => $name,
        'url'       => $home,
        'publisher' => [ '@id' => $org_id ],
        'inLanguage' => get_bloginfo( 'language' ),
    ];

    if ( theme_option( 'schema_search' ) ) {
        $website['potentialAction'] = [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => $home . '?s={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ];
    }

    $graph = [ $organization, $website ];

    return apply_filters( 'theme_schema_graph', $graph );
}

add_action( 'wp_head', 'theme_schema_head', 5 );
function theme_schema_head() {
    if ( ! theme_option( 'schema_enabled' ) || ! theme_scripts_allowed() ) {
        return;
    }

    $graph = theme_schema_graph();
    if ( empty( $graph ) ) {
        return;
    }

    // Default flags keep "/" escaped as "\/", so "</script>" can never appear inside the JSON.
    $json = wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $graph ], JSON_UNESCAPED_UNICODE );

    echo "\n<!-- Schema: Organization + WebSite -->\n<script type=\"application/ld+json\">" . $json . "</script>\n";
}
