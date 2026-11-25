<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'enjoy_enqueue_parent_styles' );
function enjoy_enqueue_parent_styles() {
    wp_enqueue_style(
        'jnews-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( 'jnews' )->get( 'Version' )
    );
}

/*
 * Replacements for the Elementor Pro pieces the site used (contact form widget,
 * newsletter popup), so the site runs without an Elementor Pro licence.
 */
require_once get_stylesheet_directory() . '/inc/contact-form.php';
require_once get_stylesheet_directory() . '/inc/newsletter-popup.php';
require_once get_stylesheet_directory() . '/inc/empty-states.php';

/*
 * Disable JNews social meta output (OG, Twitter Card, JSON-LD from jnews-meta-header plugin).
 *
 * Audit finding (2026-04-26): JNews and Rank Math both emit og:*, twitter:*, and JSON-LD
 * schema tags. JNews registers its wp_head hook unconditionally in JNews_Meta_Header::__construct()
 * at priority 1, so its tags appear first in <head>. Social crawlers (Facebook, Twitter/X,
 * LinkedIn) use the first valid occurrence — meaning JNews's mangled output wins over Rank Math's
 * correct output. The JNews UI dropdown (Customizer → Social → Social Meta Method) only offers
 * "JNews" or "Yoast" and does not check the option before firing, so the only reliable fix is
 * removing the action here. Rank Math handles all social meta correctly.
 *
 * @see jnews-meta-header/class.jnews-meta-header.php line 35
 * @see docs/audit-findings.md — SEO section, "Open Graph & Twitter Cards"
 */
add_action( 'wp', 'enjoy_disable_jnews_social_meta', 20 );
function enjoy_disable_jnews_social_meta() {
    if ( class_exists( 'JNews_Meta_Header' ) ) {
        remove_action( 'wp_head', array( JNews_Meta_Header::getInstance(), 'generate_social_meta' ), 1 );
    } else {
        error_log( 'jnews-child: JNews_Meta_Header class not found, social meta override skipped' );
    }
}

/*
 * Inject the Advanced Ads "below-header" manual placement into the JNews
 * header-bottom ad slot.
 *
 * JNews's header.php renders `<div class="jeg_ad jeg_ad_top jnews_header_bottom_ads">`
 * and fires `do_action('jnews_header_bottom_ads')` inside it. When the JNews
 * Customizer toggle (jnews_ads_header_bottom_enable) is OFF, JNews does not
 * attach its own callback to that action — leaving the wrapper empty for us.
 *
 * Advanced Ads' "Custom Position" placement type is a paid add-on, so we use
 * a free Manual Placement (slug: below-header) and call it from this hook.
 * the_ad_placement() is a no-op if the placement doesn't exist, so this
 * snippet is safe even before the placement is created in wp-admin.
 *
 * Setup: Advanced Ads → Placements → New Placement
 *        Name: Below header
 *        Type: Manual Placement
 *        Slug: below-header   (MUST match the slug passed below — Advanced Ads
 *                              stores placements as posts and WordPress auto-
 *                              hyphenates the slug from the title)
 *        Item: "Below Header Rotation" group
 */
add_action( 'jnews_header_bottom_ads', 'enjoy_inject_below_header_ad' );
function enjoy_inject_below_header_ad() {
    if ( function_exists( 'the_ad_placement' ) ) {
        the_ad_placement( 'below-header' );
    }
}

/* =============================================================================
 * AFFILIATE DISCLOSURE — Task 07
 *
 * Five pieces:
 *   1. "This post contains affiliate links" checkbox + tracking-label text input,
 *      rendered as a sidebar metabox on the post editor.
 *   2. A "Last modified" timestamp tracked for auditing.
 *   3. Inline JS that toggles the tracking-label field's disabled state in sync
 *      with the checkbox — visual signal that the label only matters when flagged.
 *   4. Server-side validation of the tracking label (kebab-case, ≤20 chars).
 *      Invalid input is rejected with an inline error rendered inside the
 *      metabox itself on next render. The existing label value is preserved.
 *
 *      We render INSIDE the metabox rather than via admin_notices because
 *      Gutenberg saves legacy metaboxes through a hidden iframe and only
 *      scrapes the metabox container out of the iframe response — anything
 *      output by admin_notices in that response is discarded before the
 *      user sees it. The metabox HTML, however, IS reflected back to the
 *      page after save, so a notice rendered as part of the metabox shows
 *      up immediately and works identically in Classic Editor.
 *
 *   5. A the_content filter that prepends the standard disclosure notice above
 *      the article body when the flag is set.
 *
 * Opt-in by design: posts without the flag get NO notice (not a content scan,
 * not a fallback — explicit editor flag only).
 *
 * Strategy: docs/affiliate-strategy.md (especially §7.4 on the tracking label).
 * Meta keys (underscore-prefixed so they don't appear in the core Custom Fields panel):
 *   _enjoy_has_affiliate_links        — '1' or '' (empty)
 *   _enjoy_affiliate_tracking_label   — kebab-case identifier, ≤20 chars
 *   _enjoy_affiliate_last_modified    — "Y-m-d H:i (user_login)" string
 * Transient key (per user + per post, 60s TTL):
 *   enjoy_affiliate_label_error_{user_id}_{post_id}
 * ============================================================================= */

// Validation pattern for tracking labels: lowercase alphanumeric kebab-case,
// no leading/trailing/double hyphens, 1-20 chars. Empty string is also allowed
// (means "no label set yet"). Defined once so render + save use the same rule.
const ENJOY_AFFILIATE_LABEL_REGEX = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
const ENJOY_AFFILIATE_LABEL_MAX   = 20;

// 1. Register the metabox on the post editor sidebar.
add_action( 'add_meta_boxes', 'enjoy_affiliate_register_metabox' );
function enjoy_affiliate_register_metabox() {
    add_meta_box(
        'enjoy_affiliate_disclosure',
        __( 'Affiliate disclosure', 'jnews-child' ),
        'enjoy_affiliate_render_metabox',
        'post',
        'side',
        'default'
    );
}

// 2. Render the metabox UI — checkbox, tracking-label input, help text,
//    last-modified stamp, optional inline error from a previous rejected
//    save, and the inline JS that ties the input's disabled state to the
//    checkbox.
function enjoy_affiliate_render_metabox( $post ) {
    $is_affiliate   = get_post_meta( $post->ID, '_enjoy_has_affiliate_links', true );
    $tracking_label = get_post_meta( $post->ID, '_enjoy_affiliate_tracking_label', true );
    $last_modified  = get_post_meta( $post->ID, '_enjoy_affiliate_last_modified', true );
    $is_checked     = ( '1' === $is_affiliate );

    // Pick up any pending validation error from a previous save.
    // We do NOT delete the transient here: Gutenberg renders this metabox
    // twice after a save — once inside the hidden meta-box-loader iframe
    // (whose HTML is discarded by gutenberg.editPost's `parse: false` fetch)
    // and once on the user's visible page when they reload or open the post.
    // If we delete on read, the invisible iframe render eats the transient
    // and the visible render finds nothing. Instead, the transient is cleared
    // by the save handler on the next successful save, and otherwise expires
    // naturally after 60s.
    $error_key = 'enjoy_affiliate_label_error_' . get_current_user_id() . '_' . $post->ID;
    $error_msg = get_transient( $error_key );
    if ( $error_msg ) {
        printf(
            '<div style="margin: 0 0 12px; padding: 8px 10px; background: #fcf0f1; border-left: 3px solid #d63638; font-size: 12px; line-height: 1.4;"><strong style="color: #d63638;">%s</strong><br><span style="color: #444;">%s</span></div>',
            esc_html__( 'Invalid tracking label', 'jnews-child' ),
            esc_html( $error_msg )
        );
    }

    wp_nonce_field( 'enjoy_affiliate_save', 'enjoy_affiliate_nonce' );
    ?>
    <p>
        <label>
            <input type="checkbox"
                   id="enjoy_has_affiliate_links"
                   name="enjoy_has_affiliate_links"
                   value="1"
                   <?php checked( $is_checked ); ?> />
            <strong><?php esc_html_e( 'This post contains affiliate links', 'jnews-child' ); ?></strong>
        </label>
    </p>
    <p class="description">
        <?php esc_html_e( 'When checked, the standard disclosure notice appears at the top of the article.', 'jnews-child' ); ?>
    </p>

    <p style="margin-top: 14px;">
        <label for="enjoy_affiliate_tracking_label" style="display:block; margin-bottom:4px;">
            <strong><?php esc_html_e( 'Tracking label', 'jnews-child' ); ?></strong>
        </label>
        <input type="text"
               id="enjoy_affiliate_tracking_label"
               name="enjoy_affiliate_tracking_label"
               value="<?php echo esc_attr( $tracking_label ); ?>"
               maxlength="<?php echo (int) ENJOY_AFFILIATE_LABEL_MAX; ?>"
               pattern="[a-z0-9]+(-[a-z0-9]+)*"
               placeholder="dubrovnik-couples"
               style="width: 100%;"
               <?php disabled( ! $is_checked ); ?> />
    </p>
    <p class="description">
        <?php
        printf(
            /* translators: %d: maximum allowed length for the tracking label */
            esc_html__( 'Kebab-case identifier, max %d characters. Lowercase letters, digits, and single hyphens only. Used in the affiliate link URL parameter (e.g. label=enjoyhr-dubrovnik-couples).', 'jnews-child' ),
            (int) ENJOY_AFFILIATE_LABEL_MAX
        );
        ?>
    </p>

    <?php if ( $last_modified ) : ?>
        <p class="description" style="margin-top: 10px; color: #666; font-size: 11px;">
            <?php
            /* translators: %s: timestamp and user that last changed the affiliate state */
            printf(
                esc_html__( 'Last modified: %s', 'jnews-child' ),
                esc_html( $last_modified )
            );
            ?>
        </p>
    <?php endif; ?>

    <script>
    (function () {
        var cb    = document.getElementById( 'enjoy_has_affiliate_links' );
        var label = document.getElementById( 'enjoy_affiliate_tracking_label' );
        if ( ! cb || ! label ) { return; }
        var sync = function () {
            label.disabled = ! cb.checked;
        };
        cb.addEventListener( 'change', sync );
        sync();
    })();
    </script>
    <?php
}

// 3. Save handler. Standard guards (nonce, autosave, capability, post type),
//    then write the checkbox value and the tracking label (with validation).
//    Timestamp is updated whenever either value changes.
add_action( 'save_post', 'enjoy_affiliate_save_metabox' );
function enjoy_affiliate_save_metabox( $post_id ) {
    if ( ! isset( $_POST['enjoy_affiliate_nonce'] ) ||
         ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['enjoy_affiliate_nonce'] ) ), 'enjoy_affiliate_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'post' ) {
        return;
    }

    $changed = false;

    // Checkbox: always save (presence = '1', absence = '').
    $new_flag = isset( $_POST['enjoy_has_affiliate_links'] ) ? '1' : '';
    $old_flag = get_post_meta( $post_id, '_enjoy_has_affiliate_links', true );
    if ( $new_flag !== $old_flag ) {
        update_post_meta( $post_id, '_enjoy_has_affiliate_links', $new_flag );
        $changed = true;
    }

    // Tracking label: sanitize then validate. Reject invalid input by setting
    // an error transient that the metabox render will display; leave the
    // previous value in place. On a valid submit, clear any prior error so
    // the metabox doesn't keep showing a stale message for up to 60s after
    // the user fixes the input.
    $error_key = 'enjoy_affiliate_label_error_' . get_current_user_id() . '_' . $post_id;
    if ( isset( $_POST['enjoy_affiliate_tracking_label'] ) ) {
        $raw_label = sanitize_text_field( wp_unslash( $_POST['enjoy_affiliate_tracking_label'] ) );
        $is_valid  = ( '' === $raw_label ) ||
                     ( strlen( $raw_label ) <= ENJOY_AFFILIATE_LABEL_MAX &&
                       preg_match( ENJOY_AFFILIATE_LABEL_REGEX, $raw_label ) );

        if ( $is_valid ) {
            $old_label = get_post_meta( $post_id, '_enjoy_affiliate_tracking_label', true );
            if ( $raw_label !== $old_label ) {
                update_post_meta( $post_id, '_enjoy_affiliate_tracking_label', $raw_label );
                $changed = true;
            }
            // Always clear any stale error on a valid submit.
            delete_transient( $error_key );
        } else {
            // Persist the rejection in a per-user + per-post transient. The
            // metabox render picks it up; we do NOT delete on render because
            // Gutenberg renders this metabox both in an invisible iframe and
            // on the user's visible page. See the render function's comment.
            set_transient(
                $error_key,
                sprintf(
                    /* translators: 1: rejected value, 2: max length */
                    __( 'You tried to save "%1$s". Use lowercase letters, digits, and single hyphens only (max %2$d characters). The previous value, if any, has been kept.', 'jnews-child' ),
                    $raw_label,
                    (int) ENJOY_AFFILIATE_LABEL_MAX
                ),
                60
            );
        }
    }

    // Stamp the audit timestamp if either field actually changed.
    if ( $changed ) {
        $user  = wp_get_current_user();
        $stamp = wp_date( 'Y-m-d H:i' );
        if ( $user && $user->user_login ) {
            $stamp .= ' (' . $user->user_login . ')';
        }
        update_post_meta( $post_id, '_enjoy_affiliate_last_modified', $stamp );
    }
}

// 4. (No separate admin_notices handler — the validation error is rendered
//    inline at the top of the metabox by step 2. See the header comment for
//    the rationale.)

// 5. Prepend the disclosure notice on flagged posts.
//    Priority 20 — after most plugin content filters, before output.
//    Idempotent: in_the_loop() + is_main_query() guards prevent double-output
//    when a theme partial re-runs the filter (e.g. RSS, related-posts widgets).
add_filter( 'the_content', 'enjoy_affiliate_disclosure_notice', 20 );
function enjoy_affiliate_disclosure_notice( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    if ( get_post_meta( get_the_ID(), '_enjoy_has_affiliate_links', true ) !== '1' ) {
        return $content;
    }

    $notice_text = __(
        'This article contains affiliate links. If you book or buy through them, Enjoy Croatia may earn a small commission at no extra cost to you. We only recommend places and experiences we would suggest to a friend visiting Croatia.',
        'jnews-child'
    );

    $notice  = '<div class="affiliate-disclosure" role="note" aria-label="' . esc_attr__( 'Affiliate disclosure', 'jnews-child' ) . '">';
    $notice .= '<p><em>' . esc_html( $notice_text ) . '</em></p>';
    $notice .= '</div>';

    return $notice . $content;
}
