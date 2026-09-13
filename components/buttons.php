<?php
/**
 * Shared buttons component.
 *
 * Reads the `buttons` repeater from the current block/post context:
 *   button_text   Text
 *   button_link   URL (string) or ACF Link array (url / title / target)
 *   button_style  Class: btn--primary (default) | btn--secondary | btn--outline | btn--outline-light
 *   button_arrow  True/false — append an arrow icon (optional sub field)
 *
 * Styles live in css/main.css (.btn and variants).
 * Usage: get_template_part( 'components/buttons' );
 */

$buttons = get_field( 'buttons' );

if ( ! $buttons ) {
    return;
}

foreach ( $buttons as $button ) {
    $text   = $button['button_text'] ?? '';
    $link   = $button['button_link'] ?? '';
    $style  = ! empty( $button['button_style'] ) ? $button['button_style'] : 'btn--primary';
    $arrow  = ! empty( $button['button_arrow'] );
    $target = '';

    // Support the ACF Link field as well as a plain URL string.
    if ( is_array( $link ) ) {
        $target = $link['target'] ?? '';
        $text   = $text ?: ( $link['title'] ?? '' );
        $link   = $link['url'] ?? '';
    }

    if ( ! $link || ! $text ) {
        continue;
    }

    $attrs = 'href="' . esc_url( $link ) . '" class="btn ' . esc_attr( $style ) . '"';
    if ( $target ) {
        $attrs .= ' target="' . esc_attr( $target ) . '" rel="noopener"';
    }

    $icon = $arrow
        ? '<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>'
        : '';

    echo '<a ' . $attrs . '>' . esc_html( $text ) . $icon . '</a>' . "\n";
}
