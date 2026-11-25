<?php
/**
 * Contact form — replaces the Elementor Pro Form widget on the Contact page.
 *
 * Usage: [enjoy_contact_form] (placed in an Elementor Shortcode widget).
 * Submissions are emailed to the site admin address; nothing is stored.
 *
 * Spam protection is a honeypot field plus a per-IP hourly throttle. No nonce:
 * the page is served from WP Super Cache to anonymous visitors, so a nonce
 * baked into cached HTML would expire and reject genuine messages.
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'enjoy_contact_form', 'enjoy_contact_form_shortcode' );
function enjoy_contact_form_shortcode() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only status flag.
    $status = isset( $_GET['enjoy_contact'] ) ? sanitize_key( wp_unslash( $_GET['enjoy_contact'] ) ) : '';

    if ( '' !== $status && ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }

    $messages = array(
        'sent'    => __( 'Thank you — your message has been sent.', 'jnews-child' ),
        'invalid' => __( 'Please fill in your name, a valid email address and a message.', 'jnews-child' ),
        'limit'   => __( 'Too many messages in a short time. Please try again later.', 'jnews-child' ),
        'error'   => __( 'Sorry, your message could not be sent. Please try again later.', 'jnews-child' ),
    );

    ob_start();
    ?>
    <div class="enjoy-contact" id="enjoy-contact-form">
        <?php if ( isset( $messages[ $status ] ) ) : ?>
            <p class="enjoy-contact__notice enjoy-contact__notice--<?php echo esc_attr( 'sent' === $status ? 'success' : 'error' ); ?>" role="status">
                <?php echo esc_html( $messages[ $status ] ); ?>
            </p>
        <?php endif; ?>

        <?php if ( 'sent' !== $status ) : ?>
        <form class="enjoy-contact__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="enjoy_contact">
            <input type="hidden" name="enjoy_return" value="<?php echo esc_url( get_permalink() ); ?>">

            <p class="enjoy-contact__field">
                <label for="enjoy-contact-name"><?php esc_html_e( 'Your Name', 'jnews-child' ); ?></label>
                <input type="text" id="enjoy-contact-name" name="enjoy_name" autocomplete="name" maxlength="100" required>
            </p>
            <p class="enjoy-contact__field">
                <label for="enjoy-contact-email"><?php esc_html_e( 'Your Email', 'jnews-child' ); ?></label>
                <input type="email" id="enjoy-contact-email" name="enjoy_email" autocomplete="email" maxlength="150" required>
            </p>
            <p class="enjoy-contact__field">
                <label for="enjoy-contact-message"><?php esc_html_e( 'Message', 'jnews-child' ); ?></label>
                <textarea id="enjoy-contact-message" name="enjoy_message" rows="6" maxlength="5000" required></textarea>
            </p>
            <p class="enjoy-contact__hp" aria-hidden="true">
                <label for="enjoy-contact-website"><?php esc_html_e( 'Leave this field empty', 'jnews-child' ); ?></label>
                <input type="text" id="enjoy-contact-website" name="enjoy_website" tabindex="-1" autocomplete="off">
            </p>
            <p class="enjoy-contact__submit">
                <button type="submit" class="btn"><?php esc_html_e( 'Send', 'jnews-child' ); ?></button>
            </p>
        </form>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_action( 'admin_post_nopriv_enjoy_contact', 'enjoy_contact_form_handle' );
add_action( 'admin_post_enjoy_contact', 'enjoy_contact_form_handle' );
function enjoy_contact_form_handle() {
    // phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; see file header.
    $return = isset( $_POST['enjoy_return'] ) ? esc_url_raw( wp_unslash( $_POST['enjoy_return'] ) ) : '';
    $return = wp_validate_redirect( $return, home_url( '/' ) );

    // Honeypot: bots that fill it get a silent "success".
    if ( ! empty( $_POST['enjoy_website'] ) ) {
        enjoy_contact_form_redirect( $return, 'sent' );
    }

    $name    = isset( $_POST['enjoy_name'] ) ? sanitize_text_field( wp_unslash( $_POST['enjoy_name'] ) ) : '';
    $email   = isset( $_POST['enjoy_email'] ) ? sanitize_email( wp_unslash( $_POST['enjoy_email'] ) ) : '';
    $message = isset( $_POST['enjoy_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['enjoy_message'] ) ) : '';
    // phpcs:enable

    $name    = mb_substr( $name, 0, 100 );
    $message = mb_substr( $message, 0, 5000 );

    if ( '' === $name || '' === $message || ! is_email( $email ) ) {
        enjoy_contact_form_redirect( $return, 'invalid' );
    }

    // Throttle: at most 5 messages per hour per IP (IP is hashed, never stored raw).
    $ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    $throttle = 'enjoy_contact_' . md5( wp_salt() . $ip );
    $count    = (int) get_transient( $throttle );
    if ( $count >= 5 ) {
        enjoy_contact_form_redirect( $return, 'limit' );
    }
    set_transient( $throttle, $count + 1, HOUR_IN_SECONDS );

    $site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    $subject = sprintf( '[%s] Contact form: message from %s', $site, $name );
    $body    = sprintf( "From: %s <%s>\n\n%s\n\n-- \nSent from the contact form on %s", $name, $email, $message, home_url( '/' ) );
    $headers = array( sprintf( 'Reply-To: %s <%s>', $name, $email ) );

    $sent = wp_mail( get_option( 'admin_email' ), $subject, $body, $headers );

    enjoy_contact_form_redirect( $return, $sent ? 'sent' : 'error' );
}

function enjoy_contact_form_redirect( $url, $status ) {
    wp_safe_redirect( add_query_arg( 'enjoy_contact', $status, $url ) . '#enjoy-contact-form' );
    exit;
}
