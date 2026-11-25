<?php
/**
 * Newsletter popup — replaces the Elementor Pro popup "Subscription Popup".
 *
 * Shows a small bottom-right panel on the front page once the visitor has
 * scrolled 60% of the page; once closed it never shows again (localStorage).
 * The signup itself is the Mailchimp for WP form.
 *
 * OFF by default. Enable per environment with:
 *   wp option update enjoy_newsletter_popup 1
 * Disable with:
 *   wp option update enjoy_newsletter_popup 0
 */

defined( 'ABSPATH' ) || exit;

function enjoy_newsletter_popup_active() {
    return is_front_page() && '1' === (string) get_option( 'enjoy_newsletter_popup', '0' ) && shortcode_exists( 'mc4wp_form' );
}

add_action( 'wp_enqueue_scripts', 'enjoy_newsletter_popup_assets' );
function enjoy_newsletter_popup_assets() {
    if ( ! enjoy_newsletter_popup_active() ) {
        return;
    }
    wp_enqueue_script(
        'enjoy-newsletter-popup',
        get_stylesheet_directory_uri() . '/assets/js/newsletter-popup.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
    );
}

add_action( 'wp_footer', 'enjoy_newsletter_popup_render' );
function enjoy_newsletter_popup_render() {
    if ( ! enjoy_newsletter_popup_active() ) {
        return;
    }
    ?>
    <aside class="enjoy-popup" id="enjoy-newsletter-popup" role="dialog" aria-labelledby="enjoy-popup-title" hidden>
        <button type="button" class="enjoy-popup__close" aria-label="<?php esc_attr_e( 'Close', 'jnews-child' ); ?>">&times;</button>
        <h2 class="enjoy-popup__title" id="enjoy-popup-title"><?php esc_html_e( 'Join Our Newsletter', 'jnews-child' ); ?></h2>
        <p class="enjoy-popup__text"><?php esc_html_e( 'Subscribe to receive our latest blog posts directly in your inbox!', 'jnews-child' ); ?></p>
        <?php echo do_shortcode( '[mc4wp_form]' ); ?>
    </aside>
    <?php
}
