<?php
/**
 * Heading class migration tool — anchor-dental theme.
 *
 * ac_migrate_heading_html( string $html ): string
 *
 * Applies the class-based heading map from docs-heading-map.md to a
 * page-content HTML fragment.  Uses PHP DOMDocument so all transforms are
 * structurally safe (no regex on markup).
 *
 * Rules (Content Migration Context Map, Table 2):
 *  - Eyebrow h3 inside .section-title → <div class="eyebrow-text"> (retag)
 *  - .expertise-list-title h3         → <div class="eyebrow-text"> (retag)
 *  - Counter h2 inside .hero-counter-box / .about-counter / .company-experience-counter
 *                                     → <div class="counter-number"> (retag)
 *  - .section-title h1/h2             → keep tag, add hero-header / content-header
 *  - .page-header-box h1              → keep tag, add hero-header
 *  - CTA containers h3                → keep tag, add cta-header
 *  - Card/component title containers h3 → keep tag, add content-subtitle
 *  - .about-doctor-info h2, .service-entry h2, etc. → keep tag, add content-header
 *  - .post-entry headings             → untouched (free-form rich text)
 *
 * Idempotent: already-classified elements are skipped.
 */

if ( ! function_exists( 'ac_migrate_heading_html' ) ) :

/**
 * Transform heading tags/classes in an HTML fragment per the class map.
 *
 * @param string $html Raw post_content HTML fragment.
 * @return string      Modified HTML fragment (or original on error).
 */
function ac_migrate_heading_html( string $html ): string {
    if ( trim( $html ) === '' ) {
        return $html;
    }

    // -----------------------------------------------------------------------
    // Container → rule map.
    // Each entry: 'container-class' => [
    //   'tags'      => ['h1','h2','h3',...],  // heading tags to match
    //   'add_class' => 'class-to-add',
    //   'new_tag'   => 'div'|null,             // null = keep original tag
    // ]
    // -----------------------------------------------------------------------
    $map = [
        // --- Eyebrow / label retags ---
        'section-title' => [
            // h3 inside .section-title → div.eyebrow-text
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'eyebrow-text',
                'new_tag'   => 'div',
            ],
            // h2 inside .section-title → h2.content-header
            [
                'tags'      => [ 'h2' ],
                'add_class' => 'content-header',
                'new_tag'   => null,
            ],
            // h1 inside .section-title → h1.hero-header
            [
                'tags'      => [ 'h1' ],
                'add_class' => 'hero-header',
                'new_tag'   => null,
            ],
        ],

        // .expertise-list-title h3 → div.eyebrow-text
        'expertise-list-title' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'eyebrow-text',
                'new_tag'   => 'div',
            ],
        ],

        // --- Counter retags ---
        'hero-counter-box' => [
            [
                'tags'      => [ 'h2', 'h3', 'h4' ],
                'add_class' => 'counter-number',
                'new_tag'   => 'div',
            ],
        ],
        'about-counter' => [
            [
                'tags'      => [ 'h2', 'h3', 'h4' ],
                'add_class' => 'counter-number',
                'new_tag'   => 'div',
            ],
        ],
        'company-experience-counter' => [
            [
                'tags'      => [ 'h2', 'h3', 'h4' ],
                'add_class' => 'counter-number',
                'new_tag'   => 'div',
            ],
        ],

        // --- CTA boxes ---
        'section-cta-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'cta-header',
                'new_tag'   => null,
            ],
        ],
        'working-box-title' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'cta-header',
                'new_tag'   => null,
            ],
        ],
        'appointment-form-title' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'cta-header',
                'new_tag'   => null,
            ],
        ],

        // --- Card / component titles (content-subtitle) ---
        'service-item-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'cta-item-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'expert-doctor-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'why-choose-item-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'post-item-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'team-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'author-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'company-founder-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'company-rating-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'contact-now-box-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'excellence-title' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'service-catagery-list' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'doctor-content' => [
            // Only applies inside .doctor-details-box — handled via ancestor check
            [
                'tags'             => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class'        => 'content-subtitle',
                'new_tag'          => null,
                'require_ancestor' => 'doctor-details-box',
            ],
        ],
        'get-touch-title' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'author-box-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'contact-info-content' => [
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],

        // --- Section-level content-header (h2, no retag) ---
        'about-doctor-info' => [
            [
                'tags'      => [ 'h2' ],
                'add_class' => 'content-header',
                'new_tag'   => null,
            ],
        ],
        'service-entry' => [
            [
                'tags'      => [ 'h2' ],
                'add_class' => 'content-header',
                'new_tag'   => null,
            ],
            [
                'tags'      => [ 'h3', 'h4', 'h5', 'h6' ],
                'add_class' => 'content-subtitle',
                'new_tag'   => null,
            ],
        ],
        'faq-accordion-title' => [
            [
                'tags'      => [ 'h2' ],
                'add_class' => 'content-header',
                'new_tag'   => null,
            ],
        ],
        'error-page-content-heading' => [
            [
                'tags'      => [ 'h2' ],
                'add_class' => 'content-header',
                'new_tag'   => null,
            ],
        ],

        // --- Hero header (h1, page banners) ---
        'page-header-box' => [
            [
                'tags'      => [ 'h1' ],
                'add_class' => 'hero-header',
                'new_tag'   => null,
            ],
        ],
    ];

    // -----------------------------------------------------------------------
    // DOM helpers
    // -----------------------------------------------------------------------

    /**
     * Return true if $el has $cls in its class attribute.
     */
    $has_class = function ( DOMElement $el, string $cls ): bool {
        $classes = preg_split( '/\s+/', trim( $el->getAttribute( 'class' ) ) );
        return in_array( $cls, $classes, true );
    };

    /**
     * Add $cls to $el's class attribute (idempotent).
     */
    $add_class = function ( DOMElement $el, string $cls ) use ( $has_class ): void {
        if ( $has_class( $el, $cls ) ) {
            return;
        }
        $existing = trim( $el->getAttribute( 'class' ) );
        $el->setAttribute( 'class', $existing !== '' ? $existing . ' ' . $cls : $cls );
    };

    /**
     * Walk ancestors; return true if any ancestor has $cls.
     */
    $ancestor_has_class = function ( DOMNode $node, string $cls ) use ( &$ancestor_has_class, $has_class ): bool {
        $parent = $node->parentNode;
        while ( $parent && $parent->nodeType === XML_ELEMENT_NODE ) {
            /** @var DOMElement $parent */
            if ( $has_class( $parent, $cls ) ) {
                return true;
            }
            $parent = $parent->parentNode;
        }
        return false;
    };

    /**
     * Nearest ancestor that has any of the given classes; returns that
     * ancestor element and which class matched, or null.
     */
    $nearest_ancestor_with_classes = function ( DOMNode $node, array $classes ) use ( $has_class ): ?array {
        $parent = $node->parentNode;
        while ( $parent && $parent->nodeType === XML_ELEMENT_NODE ) {
            /** @var DOMElement $parent */
            foreach ( $classes as $cls ) {
                if ( $has_class( $parent, $cls ) ) {
                    return [ 'el' => $parent, 'class' => $cls ];
                }
            }
            $parent = $parent->parentNode;
        }
        return null;
    };

    // -----------------------------------------------------------------------
    // Load HTML into DOMDocument
    // -----------------------------------------------------------------------
    $wrap_id    = 'ac-migrate-root-' . substr( md5( $html ), 0, 8 );
    $wrapped    = '<div id="' . $wrap_id . '">' . $html . '</div>';

    $doc = new DOMDocument( '1.0', 'UTF-8' );
    libxml_use_internal_errors( true );
    $doc->loadHTML(
        '<?xml encoding="UTF-8">' . $wrapped,
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    // Find our wrapper
    $root = $doc->getElementById( $wrap_id );
    if ( ! $root ) {
        // Fallback: find by tag + id attribute manually
        $divs = $doc->getElementsByTagName( 'div' );
        foreach ( $divs as $div ) {
            if ( $div->getAttribute( 'id' ) === $wrap_id ) {
                $root = $div;
                break;
            }
        }
    }
    if ( ! $root ) {
        return $html; // Could not parse — return original.
    }

    // -----------------------------------------------------------------------
    // Collect all heading nodes (snapshot so DOM mutations don't affect walk)
    // -----------------------------------------------------------------------
    $heading_tags  = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
    $container_classes = array_keys( $map );

    $nodes_to_process = [];
    foreach ( $heading_tags as $tag ) {
        $list = $root->getElementsByTagName( $tag );
        foreach ( $list as $node ) {
            $nodes_to_process[] = $node;
        }
    }

    // Nodes that need retag (collected first, applied after iteration)
    $retags = []; // [ node, new_tag, add_class ]

    foreach ( $nodes_to_process as $node ) {
        /** @var DOMElement $node */
        $tag = strtolower( $node->nodeName );

        // Skip if inside .post-entry (free-form rich text — leave untouched)
        if ( $ancestor_has_class( $node, 'post-entry' ) ) {
            continue;
        }

        // Find nearest relevant ancestor container
        $match = $nearest_ancestor_with_classes( $node, $container_classes );
        if ( ! $match ) {
            continue; // No mapped context — leave untouched.
        }

        $container_cls = $match['class'];
        $rules         = $map[ $container_cls ];

        foreach ( $rules as $rule ) {
            // Check optional ancestor requirement
            if ( isset( $rule['require_ancestor'] ) ) {
                if ( ! $ancestor_has_class( $node, $rule['require_ancestor'] ) ) {
                    continue;
                }
            }

            if ( ! in_array( $tag, $rule['tags'], true ) ) {
                continue;
            }

            $target_class = $rule['add_class'];
            $new_tag      = $rule['new_tag'];

            // Idempotency check
            if ( $has_class( $node, $target_class ) ) {
                break; // Already done.
            }

            if ( $new_tag !== null && strtolower( $new_tag ) !== $tag ) {
                // Schedule a retag
                $retags[] = [ $node, $new_tag, $target_class ];
            } else {
                // Just add the class
                $add_class( $node, $target_class );
            }
            break; // First matching rule wins.
        }
    }

    // -----------------------------------------------------------------------
    // Apply retags (replace element with new tag, preserve children + attrs)
    // -----------------------------------------------------------------------
    foreach ( $retags as [ $node, $new_tag, $target_class ] ) {
        /** @var DOMElement $node */
        // Idempotency: if node was already retagged (e.g. already div.eyebrow-text), skip
        if ( $has_class( $node, $target_class ) ) {
            continue;
        }

        $new_el = $doc->createElement( $new_tag );

        // Copy attributes
        foreach ( $node->attributes as $attr ) {
            $new_el->setAttribute( $attr->name, $attr->value );
        }

        // Merge/set class
        $existing_cls = trim( $new_el->getAttribute( 'class' ) );
        if ( $existing_cls !== '' ) {
            // Preserve existing classes, append target if not present
            $cls_list = preg_split( '/\s+/', $existing_cls );
            if ( ! in_array( $target_class, $cls_list, true ) ) {
                $cls_list[] = $target_class;
            }
            $new_el->setAttribute( 'class', implode( ' ', $cls_list ) );
        } else {
            $new_el->setAttribute( 'class', $target_class );
        }

        // Move children
        while ( $node->firstChild ) {
            $new_el->appendChild( $node->firstChild );
        }

        // Replace in tree
        $node->parentNode->replaceChild( $new_el, $node );
    }

    // -----------------------------------------------------------------------
    // Serialize: extract innerHTML of root wrapper only
    // -----------------------------------------------------------------------
    $output = '';
    foreach ( $root->childNodes as $child ) {
        $output .= $doc->saveHTML( $child );
    }

    // Remove the leading xml processing-instruction if DOMDocument injected one.
    $output = preg_replace( '/^<[?]xml[^?]*[?]>\s*/i', '', $output );

    return $output;
}

endif; // function_exists
