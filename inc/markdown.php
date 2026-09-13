<?php
/**
 * Minimal Markdown → HTML converter for the theme's own documentation pages.
 *
 * Deliberately small. It supports the syntax used in the theme's .md files:
 *   # headings (h1–h6, with id attributes for anchor links)
 *   paragraphs, --- horizontal rules
 *   - / * bullet lists, 1. numbered lists, nested by indentation, - [ ] task items
 *   | tables | with a |---| separator row
 *   ``` fenced code blocks (language hint kept as a class)
 *   inline `code`, **bold**, [links](url)
 *
 * Everything is escaped before inline formatting is applied, so the source
 * file cannot inject HTML. Not a general-purpose parser: do not use it for
 * user-submitted content.
 *
 * Usage:
 *   $doc = theme_markdown_to_html( file_get_contents( $path ) );
 *   echo $doc['html'];           // the document
 *   foreach ( $doc['headings'] as $h ) { ... } // [ 'level' => 2, 'text' => '...', 'id' => '...' ]
 */

if ( ! function_exists( 'theme_markdown_to_html' ) ) :

function theme_markdown_to_html( $markdown ) {
    $lines    = preg_split( "/\r\n|\r|\n/", (string) $markdown );
    $out      = [];
    $headings = [];
    $ids      = [];
    $i        = 0;
    $n        = count( $lines );

    while ( $i < $n ) {
        $line = $lines[ $i ];

        // Blank line: nothing to do.
        if ( '' === trim( $line ) ) {
            $i++;
            continue;
        }

        // Fenced code block.
        if ( preg_match( '/^```\s*([\w-]*)\s*$/', $line, $m ) ) {
            $lang = $m[1];
            $code = [];
            $i++;
            while ( $i < $n && ! preg_match( '/^```\s*$/', $lines[ $i ] ) ) {
                $code[] = $lines[ $i ];
                $i++;
            }
            $i++; // closing fence
            $class = $lang ? ' class="language-' . esc_attr( $lang ) . '"' : '';
            $out[] = '<pre><code' . $class . '>' . esc_html( implode( "\n", $code ) ) . '</code></pre>';
            continue;
        }

        // Heading.
        if ( preg_match( '/^(#{1,6})\s+(.+?)\s*#*\s*$/', $line, $m ) ) {
            $level = strlen( $m[1] );
            $text  = trim( $m[2] );
            $id    = theme_markdown_slug( $text, $ids );
            $headings[] = [ 'level' => $level, 'text' => $text, 'id' => $id ];
            $out[] = sprintf( '<h%1$d id="%2$s">%3$s</h%1$d>', $level, esc_attr( $id ), theme_markdown_inline( $text ) );
            $i++;
            continue;
        }

        // Horizontal rule.
        if ( preg_match( '/^\s*([-*_])(\s*\1){2,}\s*$/', $line ) ) {
            $out[] = '<hr>';
            $i++;
            continue;
        }

        // Table: a header row followed by a separator row.
        if ( strpos( ltrim( $line ), '|' ) === 0 && isset( $lines[ $i + 1 ] ) && preg_match( '/^\s*\|?\s*:?-{2,}/', $lines[ $i + 1 ] ) ) {
            $header = theme_markdown_table_cells( $line );
            $i     += 2;
            $rows   = [];
            while ( $i < $n && strpos( ltrim( $lines[ $i ] ), '|' ) === 0 ) {
                $rows[] = theme_markdown_table_cells( $lines[ $i ] );
                $i++;
            }
            $html = '<div class="table-wrap"><table><thead><tr>';
            foreach ( $header as $cell ) {
                $html .= '<th>' . theme_markdown_inline( $cell ) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ( $rows as $row ) {
                $html .= '<tr>';
                foreach ( $header as $k => $unused ) {
                    $html .= '<td>' . theme_markdown_inline( $row[ $k ] ?? '' ) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            $out[] = $html;
            continue;
        }

        // List (bulleted, numbered, or task). Collect the whole block first.
        if ( preg_match( '/^(\s*)([-*+]|\d+[.)])\s+/', $line ) ) {
            $block = [];
            while ( $i < $n && '' !== trim( $lines[ $i ] ) ) {
                $block[] = $lines[ $i ];
                $i++;
            }
            // A list may contain blank lines between items; keep going if the next
            // non-blank line is still an indented list item or continuation.
            while ( $i < $n ) {
                $j = $i;
                while ( $j < $n && '' === trim( $lines[ $j ] ) ) {
                    $j++;
                }
                if ( $j < $n && preg_match( '/^(\s+)([-*+]|\d+[.)])\s+|^(\s{2,})\S/', $lines[ $j ] ) ) {
                    for ( $k = $i; $k <= $j; $k++ ) {
                        $block[] = $lines[ $k ];
                    }
                    $i = $j + 1;
                    while ( $i < $n && '' !== trim( $lines[ $i ] ) ) {
                        $block[] = $lines[ $i ];
                        $i++;
                    }
                } else {
                    break;
                }
            }
            $out[] = theme_markdown_list( $block );
            continue;
        }

        // Paragraph: consecutive non-blank lines that are not another block type.
        $para = [];
        while ( $i < $n && '' !== trim( $lines[ $i ] )
            && ! preg_match( '/^(#{1,6})\s+|^```|^\s*([-*+]|\d+[.)])\s+|^\s*\|/', $lines[ $i ] )
            && ! preg_match( '/^\s*([-*_])(\s*\1){2,}\s*$/', $lines[ $i ] ) ) {
            $para[] = trim( $lines[ $i ] );
            $i++;
        }
        if ( $para ) {
            $out[] = '<p>' . theme_markdown_inline( implode( ' ', $para ) ) . '</p>';
        } else {
            // Safety net so an unexpected line can never cause an infinite loop.
            $out[] = '<p>' . theme_markdown_inline( trim( $line ) ) . '</p>';
            $i++;
        }
    }

    return [
        'html'     => implode( "\n", $out ),
        'headings' => $headings,
    ];
}

/**
 * Build nested <ul>/<ol> markup from a block of list lines.
 */
function theme_markdown_list( array $lines ) {
    $items = []; // each: [ 'indent' => int, 'ordered' => bool, 'text' => string ]

    foreach ( $lines as $line ) {
        if ( preg_match( '/^(\s*)([-*+]|\d+[.)])\s+(.*)$/', $line, $m ) ) {
            $items[] = [
                'indent'  => strlen( str_replace( "\t", '    ', $m[1] ) ),
                'ordered' => (bool) preg_match( '/^\d/', $m[2] ),
                'number'  => (int) $m[2],
                'text'    => $m[3],
            ];
        } elseif ( $items && '' !== trim( $line ) ) {
            // Continuation line: append to the previous item.
            $items[ count( $items ) - 1 ]['text'] .= ' ' . trim( $line );
        }
    }

    $pos = 0;
    return theme_markdown_list_level( $items, $pos, $items ? $items[0]['indent'] : 0 );
}

function theme_markdown_list_level( array $items, &$pos, $indent ) {
    if ( ! isset( $items[ $pos ] ) ) {
        return '';
    }

    $tag  = $items[ $pos ]['ordered'] ? 'ol' : 'ul';
    $html = '<' . $tag;
    // A numbered list that resumes after a table or code block keeps its numbering.
    if ( $items[ $pos ]['ordered'] && $items[ $pos ]['number'] > 1 ) {
        $html .= ' start="' . (int) $items[ $pos ]['number'] . '"';
    }
    $html .= '>';

    while ( isset( $items[ $pos ] ) && $items[ $pos ]['indent'] >= $indent ) {
        $item = $items[ $pos ];

        if ( $item['indent'] > $indent ) {
            // Deeper level belongs inside the previous <li>; back up and nest.
            $nested = theme_markdown_list_level( $items, $pos, $item['indent'] );
            $html   = preg_replace( '/<\/li>$/', $nested . '</li>', $html, 1 );
            continue;
        }

        $text  = $item['text'];
        $class = '';
        if ( preg_match( '/^\[( |x|X)\]\s+(.*)$/', $text, $m ) ) {
            $checked = 'x' === strtolower( $m[1] );
            $text    = '<input type="checkbox" disabled' . ( $checked ? ' checked' : '' ) . '> ' . theme_markdown_inline( $m[2] );
            $class   = ' class="task"';
        } else {
            $text = theme_markdown_inline( $text );
        }

        $html .= '<li' . $class . '>' . $text . '</li>';
        $pos++;
    }

    return $html . '</' . $tag . '>';
}

/**
 * Split a table row into trimmed cells.
 */
function theme_markdown_table_cells( $line ) {
    $line = trim( $line );
    $line = preg_replace( '/^\||\|$/', '', $line );
    return array_map( 'trim', explode( '|', $line ) );
}

/**
 * Inline formatting on an escaped string: code spans, bold, links.
 */
function theme_markdown_inline( $text ) {
    $text = esc_html( $text );

    // Code spans first so their contents are left alone by the other rules.
    $spans = [];
    $text  = preg_replace_callback( '/`([^`]+)`/', function ( $m ) use ( &$spans ) {
        $spans[] = '<code>' . $m[1] . '</code>';
        return "\x1A" . ( count( $spans ) - 1 ) . "\x1A";
    }, $text );

    $text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
    $text = preg_replace_callback( '/\[([^\]]+)\]\(([^)\s]+)\)/', function ( $m ) {
        return '<a href="' . esc_url( html_entity_decode( $m[2] ) ) . '">' . $m[1] . '</a>';
    }, $text );

    return preg_replace_callback( "/\x1A(\d+)\x1A/", function ( $m ) use ( $spans ) {
        return $spans[ (int) $m[1] ];
    }, $text );
}

/**
 * Unique, URL-safe id for a heading.
 */
function theme_markdown_slug( $text, array &$used ) {
    $slug = sanitize_title( wp_strip_all_tags( preg_replace( '/`|\*\*/', '', $text ) ) );
    $slug = $slug ?: 'section';
    $base = $slug;
    $k    = 2;
    while ( isset( $used[ $slug ] ) ) {
        $slug = $base . '-' . $k++;
    }
    $used[ $slug ] = true;
    return $slug;
}

endif;
