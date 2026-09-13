<!DOCTYPE html>
<html <?php language_attributes(); ?> data-wf-page="69e9b04112b480ddc79618a5" data-wf-site="69e9b04112b480ddc79618ae">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <?php if ( $thumb = get_field( 'site_thumbnail', 'option' ) ) : ?>
  <meta name="thumbnail" content="<?php echo esc_url( $thumb ); ?>" />
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
  <?php // Entrance animations: hide animated elements before first paint so they don't flash. Skipped for reduced-motion users and browsers without IntersectionObserver. ?>
  <script>if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.classList.add('has-anim');}</script>
  <link href="<?php echo get_template_directory_uri(); ?>/images/favicon.png" rel="shortcut icon" type="image/x-icon">
  <link href="<?php echo get_template_directory_uri(); ?>/images/webclip.png" rel="apple-touch-icon">
  <?php
  /*
   * Header scripts from Site Content → Site Scripts are output on wp_head (priority 1)
   * by theme_output_header_scripts() in functions.php. Body and footer scripts use
   * wp_body_open and wp_footer. Nothing is echoed directly in this template.
   */
  wp_head();
  ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'components/blocks/parts/nav' ); ?>
