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
