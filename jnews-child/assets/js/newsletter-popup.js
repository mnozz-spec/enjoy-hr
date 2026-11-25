/**
 * Newsletter popup behaviour. See inc/newsletter-popup.php.
 */
( function () {
    'use strict';

    var popup = document.getElementById( 'enjoy-newsletter-popup' );
    if ( ! popup ) {
        return;
    }

    var KEY = 'enjoyNewsletterPopupClosed';
    var SCROLL_TRIGGER = 0.6;

    function wasClosed() {
        try {
            return '1' === window.localStorage.getItem( KEY );
        } catch ( e ) {
            return false;
        }
    }

    function open() {
        popup.hidden = false;
        // Next frame so the CSS transition runs.
        window.requestAnimationFrame( function () {
            popup.classList.add( 'is-open' );
        } );
        window.removeEventListener( 'scroll', onScroll );
    }

    function close() {
        popup.classList.remove( 'is-open' );
        popup.hidden = true;
        try {
            window.localStorage.setItem( KEY, '1' );
        } catch ( e ) {
            // Storage unavailable: the popup may show again on the next visit.
        }
    }

    function onScroll() {
        var doc = document.documentElement;
        var scrollable = doc.scrollHeight - window.innerHeight;
        if ( scrollable <= 0 || window.scrollY / scrollable >= SCROLL_TRIGGER ) {
            open();
        }
    }

    popup.querySelector( '.enjoy-popup__close' ).addEventListener( 'click', close );
    document.addEventListener( 'keydown', function ( event ) {
        if ( 'Escape' === event.key && ! popup.hidden ) {
            close();
        }
    } );

    // After a signup the page reloads with Mailchimp's response inside the form:
    // show it straight away, even if the popup was closed before.
    var response = popup.querySelector( '.mc4wp-response' );
    if ( response && response.textContent.trim() ) {
        open();
        return;
    }

    if ( wasClosed() ) {
        return;
    }

    window.addEventListener( 'scroll', onScroll, { passive: true } );
}() );
