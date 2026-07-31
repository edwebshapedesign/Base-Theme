<!DOCTYPE html>
<html lang="en-GB" data-wf-page="69e9b04112b480ddc79618a5" data-wf-site="69e9b04112b480ddc79618ae">
<head>
    <?php $headerscripts = get_field('header_scripts','option'); ?>
        <?php if($headerscripts): ?>
          <?php foreach($headerscripts as $headerscript): ?>

<?php echo $headerscript['script']; ?>

<?php endforeach; ?>
<?php endif; ?>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="thumbnail" content="<?php echo get_field('site_thumbnail', 'option'); ?>" />
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
  <link href="<?php echo get_template_directory_uri(); ?>/images/favicon.png" rel="shortcut icon" type="image/x-icon">
  <link href="<?php echo get_template_directory_uri(); ?>/images/webclip.png" rel="apple-touch-icon">
  <?php wp_head(); ?>
  <script>
    window.onload = function() {
        var anchors = document.getElementsByTagName('*');
        for(var i = 0; i < anchors.length; i++) {
            var anchor = anchors[i];
            anchor.onclick = function() {
                code = this.getAttribute('whenClicked');
                eval(code);   
            }
        }
    }
</script>

<?php

global $lumisol_schema_memberof;

$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => get_bloginfo('name'),
    'url'      => home_url('/'),
];

if (!empty($lumisol_schema_memberof)) {
    $schema['memberOf'] = $lumisol_schema_memberof;
}

?>

<script type="application/ld+json">
<?php
echo wp_json_encode(
    $schema,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
?>
</script>

</head>
<body>
    <?php $bodyscripts = get_field('body_scripts','option'); ?>
        <?php if($bodyscripts): ?>
          <?php foreach($bodyscripts as $bodyscript): ?>

<?php echo $bodyscript['script']; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php get_template_part('components/blocks/parts/nav'); ?>  
    
    
	