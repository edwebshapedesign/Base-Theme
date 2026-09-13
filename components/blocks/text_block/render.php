<?php
/**
 * Text Block (text_block pattern from the block library).
 *
 * The general purpose content section. One `layout` select switches between
 * six arrangements:
 *
 *   simple              Centred title, restricted-width text below
 *   simple-columns      As simple, but the text flows into two CSS columns
 *   columns             Image left, text right
 *   columns-reverse     Text left, image right
 *   bk-columns          Full-height background image spacer left, text right
 *   bk-columns-reverse  Text left, background image spacer right
 *
 * Available variables (provided by ACF):
 *   $block, $content, $is_preview, $post_id
 *
 * Fields (see acf-json/group_block_text_block.json):
 *   title, post_title, text, bullets (repeater: bold, text),
 *   buttons (repeater: button_text, button_link, button_style, button_arrow),
 *   layout, image, background
 *   Plus the cloned Block Settings: block_visible, padding_top, padding_bottom
 */

// Inserter preview: block.json "example" passes preview_image_help, so show the
// static screenshot instead of rendering the block when one exists.
if ( ! empty( $block['data']['preview_image_help'] ) ) {
    $preview_file = basename( $block['data']['preview_image_help'] );

    if ( file_exists( __DIR__ . '/' . $preview_file ) ) {
        $preview_url = get_template_directory_uri() . '/components/blocks/text_block/' . $preview_file;
        echo '<img src="' . esc_url( $preview_url ) . '" alt="' . esc_attr__( 'Text Block preview', 'base-theme' ) . '" style="width:100%;height:auto;display:block;">';
        return;
    }
}

// ---------------------------------------------------------------------
// Fields
// ---------------------------------------------------------------------
$title      = get_field( 'title' );
$post_title = get_field( 'post_title' );
$text       = get_field( 'text' );
$bullets    = get_field( 'bullets' );
$buttons    = get_field( 'buttons' );
$layout     = get_field( 'layout' );
$image_id   = (int) get_field( 'image' );
$background = get_field( 'background' );

$layouts = [ 'simple', 'simple-columns', 'columns', 'columns-reverse', 'bk-columns', 'bk-columns-reverse' ];
$layout  = in_array( $layout, $layouts, true ) ? $layout : 'simple';

$backgrounds = [ 'white', 'light', 'dark' ];
$background  = in_array( $background, $backgrounds, true ) ? $background : 'white';

$is_column_layout = 0 === strpos( $layout, 'columns' ) || 0 === strpos( $layout, 'bk-columns' );
$is_spacer_layout = 0 === strpos( $layout, 'bk-columns' );
$is_reverse       = '-reverse' === substr( $layout, -8 );

// Only column layouts show the image.
if ( ! $is_column_layout ) {
    $image_id = 0;
}

// Editor empty state so a brand-new block is visible while editing.
if ( $is_preview && ! $title && ! $post_title && ! $text && ! $bullets && ! $image_id ) {
    $title = __( 'Text Block', 'base-theme' );
    $text  = '<p>' . __( 'Add a title, some text and optional bullets, buttons and an image in the block settings panel. Change the layout from the Layout tab.', 'base-theme' ) . '</p>';
}

// Nothing to show on the front end.
if ( ! $title && ! $post_title && ! $text && ! $bullets && ! $image_id && ! $buttons ) {
    return;
}

$classes = [
    'text-block',
    'text-block--' . $layout,
    'text-block--bg-' . $background,
];
if ( $image_id ) {
    $classes[] = 'has-image';
}

// ---------------------------------------------------------------------
// Content column: title, highlight, text, checklist, buttons.
// Buffered so the column layouts can output it before or after the image.
// ---------------------------------------------------------------------
ob_start();
?>
    <?php if ( $title ) : ?>
        <div class="text-block__title-holder">
            <h2 class="text-block__title" data-animate><?php echo esc_html( $title ); ?></h2>
        </div>
    <?php endif; ?>

    <?php if ( $post_title || $text || $bullets ) : ?>
        <div class="text-block__content" data-animate>
            <?php if ( $post_title ) : ?>
                <p class="text-block__highlight"><?php echo esc_html( $post_title ); ?></p>
            <?php endif; ?>

            <?php if ( $text ) : ?>
                <div class="text-block__body">
                    <?php echo wp_kses_post( $text ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $bullets ) : ?>
                <ul class="text-block__checklist">
                    <?php foreach ( $bullets as $bullet ) : ?>
                        <?php
                        $bold      = $bullet['bold'] ?? '';
                        $item_text = $bullet['text'] ?? '';
                        if ( ! $bold && ! $item_text ) {
                            continue;
                        }
                        ?>
                        <li class="text-block__checklist-item">
                            <span class="text-block__check" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span>
                                <?php if ( $bold ) : ?><strong><?php echo esc_html( $bold ); ?></strong> <?php endif; ?>
                                <?php echo esc_html( $item_text ); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( $buttons ) : ?>
        <div class="button-holder<?php echo $is_column_layout ? '' : ' center'; ?>" data-animate>
            <?php get_template_part( 'components/buttons' ); ?>
        </div>
    <?php endif; ?>
<?php
$content_html = ob_get_clean();

// ---------------------------------------------------------------------
// Media column (column layouts only).
// ---------------------------------------------------------------------
$media_html = '';
if ( $is_column_layout && $image_id ) {
    if ( $is_spacer_layout ) {
        $spacer_url = wp_get_attachment_image_url( $image_id, 'full' );
        $spacer_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
        if ( $spacer_url ) {
            $media_html  = '<div class="text-block__image-spacer" role="img" aria-label="' . esc_attr( $spacer_alt ) . '"';
            $media_html .= ' style="background-image:url(' . esc_url( $spacer_url ) . ');"></div>';
        }
    } else {
        $media_html = wp_get_attachment_image(
            $image_id,
            'large',
            false,
            [
                'class'    => 'text-block__image',
                'loading'  => 'lazy',
                'decoding' => 'async',
                'sizes'    => '(max-width: 991px) 92vw, 50vw',
            ]
        );
    }
}
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => theme_block_settings_classes( $classes ) ] ); ?>>
    <div class="container">
        <?php if ( $is_column_layout ) : ?>
            <div class="text-block__columns<?php echo $is_reverse ? ' text-block__columns--reverse' : ''; ?>">
                <?php if ( $is_reverse ) : ?>
                    <div class="text-block__column text-block__column--content"><?php echo $content_html; ?></div>
                    <?php if ( $media_html ) : ?>
                        <div class="text-block__column text-block__column--media" data-animate><?php echo $media_html; ?></div>
                    <?php endif; ?>
                <?php else : ?>
                    <?php if ( $media_html ) : ?>
                        <div class="text-block__column text-block__column--media" data-animate><?php echo $media_html; ?></div>
                    <?php endif; ?>
                    <div class="text-block__column text-block__column--content"><?php echo $content_html; ?></div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="text-block__simple<?php echo 'simple-columns' === $layout ? ' text-block__simple--2-cols' : ''; ?>">
                <?php echo $content_html; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
