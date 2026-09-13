<?php
/**
 * Template part: site header + primary navigation.
 *
 * Reads:
 *   site_logo  (Site Content → General → Branding)  image ID
 *   main-menu  nav menu location (Appearance → Menus)
 *
 * Styles: css/navigation.css   Behaviour: js/navigation.js
 */

$logo_id   = (int) get_field( 'site_logo', 'option' );
$site_name = get_bloginfo( 'name' );
$home_url  = home_url( '/' );

// Logo alt: Media Library alt text, else the site name.
$logo_alt = $logo_id ? get_post_meta( $logo_id, '_wp_attachment_image_alt', true ) : '';
$logo_alt = $logo_alt ?: $site_name;

$has_menu = has_nav_menu( 'main-menu' );
?>
<header class="site-header" id="site-header">
    <div class="container site-header__inner">

        <a class="site-logo" href="<?php echo esc_url( $home_url ); ?>" rel="home"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
            <?php if ( $logo_id ) : ?>
                <?php
                echo wp_get_attachment_image(
                    $logo_id,
                    'medium',
                    false,
                    [
                        'class'         => 'site-logo__img',
                        'alt'           => $logo_alt,
                        'loading'       => 'eager',
                        'fetchpriority' => 'high',
                        'decoding'      => 'async',
                    ]
                );
                ?>
            <?php else : ?>
                <span class="site-logo__text"><?php echo esc_html( $site_name ); ?></span>
            <?php endif; ?>
        </a>

        <button class="site-nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'base-theme' ); ?>" data-label-open="<?php esc_attr_e( 'Open menu', 'base-theme' ); ?>" data-label-close="<?php esc_attr_e( 'Close menu', 'base-theme' ); ?>">
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
            <span class="site-nav-toggle__bar" aria-hidden="true"></span>
        </button>

        <nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main', 'base-theme' ); ?>">
            <?php
            if ( $has_menu ) {
                wp_nav_menu(
                    [
                        'theme_location' => 'main-menu',
                        'container'      => false,
                        'menu_class'     => 'site-nav__list',
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ]
                );
            } else {
                // No menu assigned yet: list top-level pages so the header is never empty.
                wp_page_menu(
                    [
                        'menu_class'  => 'site-nav__fallback',
                        'container'   => 'div',
                        'before'      => '<ul class="site-nav__list">',
                        'after'       => '</ul>',
                        'show_home'   => true,
                        'depth'       => 1,
                        'sort_column' => 'menu_order, post_title',
                    ]
                );
                if ( current_user_can( 'edit_theme_options' ) ) {
                    echo '<p class="site-nav__notice">' . sprintf(
                        /* translators: %s: link to Appearance → Menus */
                        esc_html__( 'Showing pages. Assign a menu to “Main Menu” under %s.', 'base-theme' ),
                        '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Appearance → Menus', 'base-theme' ) . '</a>'
                    ) . '</p>';
                }
            }
            ?>
        </nav>

    </div>
</header>
