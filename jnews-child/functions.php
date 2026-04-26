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
