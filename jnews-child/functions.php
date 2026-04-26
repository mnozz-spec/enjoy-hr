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
