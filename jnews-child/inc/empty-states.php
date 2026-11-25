<?php
/**
 * Empty states — 404, empty category/tag/date archives and no-result searches.
 *
 * JNews prints a bare "No Content Available" box (.jeg_empty_module, from
 * ModuleViewAbstract::empty_content()) when an archive query has no posts.
 * The child templates (404.php, category.php, archive.php, search.php) call
 * enjoy_render_empty_state() instead of the JNews module in that one case.
 *
 * The JNews filter `jnews_module_no_content` is deliberately not used: it
 * fires for every module on the site (homepage blocks, widgets, AJAX tabs),
 * not only for the main archive content.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the main query is an archive or search with no posts at all.
 *
 * Paged requests are excluded so only a truly empty first page qualifies.
 *
 * @return bool
 */
function enjoy_is_empty_archive() {
    global $wp_query;

    if ( ! ( $wp_query instanceof WP_Query ) || is_paged() ) {
        return false;
    }

    return ( is_archive() || is_search() ) && 0 === (int) $wp_query->found_posts;
}

add_action( 'wp_enqueue_scripts', 'enjoy_empty_state_assets' );
/**
 * Load the empty-state stylesheet only on the views that render one.
 */
function enjoy_empty_state_assets() {
    if ( ! is_404() && ! enjoy_is_empty_archive() ) {
        return;
    }

    wp_enqueue_style(
        'enjoy-empty-states',
        get_stylesheet_directory_uri() . '/assets/css/empty-states.css',
        array(),
        wp_get_theme()->get( 'Version' )
    );
}

/**
 * Fetch the guides listed in an empty state.
 *
 * With a search term, returns posts whose title or content mention it; if
 * there are none (or no term was given), returns the latest posts.
 *
 * @param string $term  Optional. Plain-text term to look for.
 * @param int    $count Optional. Maximum number of posts.
 * @return array {
 *     @type WP_Post[] $posts   Posts to list.
 *     @type bool      $related True when the posts matched $term.
 * }
 */
function enjoy_empty_state_posts( $term = '', $count = 6 ) {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => max( 1, (int) $count ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    );

    $term = trim( wp_strip_all_tags( (string) $term ) );

    if ( '' !== $term ) {
        $related = get_posts( array_merge( $args, array( 's' => $term ) ) );

        if ( ! empty( $related ) ) {
            return array(
                'posts'   => $related,
                'related' => true,
            );
        }
    }

    return array(
        'posts'   => get_posts( $args ),
        'related' => false,
    );
}

/**
 * Print a titled list of guide links.
 *
 * @param WP_Post[] $posts   Posts to list.
 * @param string    $heading Heading text (plain text, escaped here).
 */
function enjoy_empty_state_list( $posts, $heading ) {
    if ( empty( $posts ) ) {
        return;
    }
    ?>
    <div class="enjoy-empty__guides jeg_postblock">
        <div class="jeg_block_heading jeg_block_heading_6">
            <h2 class="jeg_block_title"><span><?php echo esc_html( $heading ); ?></span></h2>
        </div>
        <ul class="enjoy-empty__list">
            <?php foreach ( $posts as $guide ) : ?>
                <li class="jeg_post_title">
                    <a href="<?php echo esc_url( get_permalink( $guide ) ); ?>"><?php echo esc_html( wp_strip_all_tags( get_the_title( $guide ) ) ); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    // get_posts() does not touch the global $post, but templates around this
    // call rely on it, so restore it defensively.
    wp_reset_postdata();
}

/**
 * Render the empty state for the current view.
 *
 * @param string $context One of '404', 'search', 'category' (any term
 *                        archive) or 'archive' (date, author, anything else).
 */
function enjoy_render_empty_state( $context = 'archive' ) {
    $context = sanitize_key( $context );
    $title   = '';
    $text    = '';
    $term    = '';
    $form    = true;

    switch ( $context ) {
        case '404':
            // 404.php prints its own heading, text and search form.
            $form = false;
            break;

        case 'search':
            // search.php prints the "Nothing found" heading and the search form.
            $form = false;
            $text = __( 'Check the spelling or try a shorter, more general term, such as a town, an island or "ferry".', 'jnews-child' );
            break;

        case 'category':
            $queried = get_queried_object();

            if ( $queried instanceof WP_Term ) {
                $term = wp_strip_all_tags( $queried->name );
                /* translators: %s: category or tag name. */
                $title = sprintf( __( 'No guides for %s yet', 'jnews-child' ), $term );
            } else {
                $title = __( 'No guides here yet', 'jnews-child' );
            }
            $text = __( 'We have not published anything in this section so far. Search the site, or start with one of the guides below.', 'jnews-child' );
            break;

        default:
            $title = __( 'No guides here yet', 'jnews-child' );
            $text  = __( 'Nothing has been published here so far. Search the site, or start with one of the guides below.', 'jnews-child' );
            break;
    }

    $found = enjoy_empty_state_posts( $term );

    if ( $found['related'] ) {
        /* translators: %s: category or tag name. */
        $heading = sprintf( __( 'Guides that mention %s', 'jnews-child' ), $term );
    } else {
        $heading = __( 'Latest guides', 'jnews-child' );
    }
    ?>
    <div class="enjoy-empty enjoy-empty--<?php echo esc_attr( $context ); ?>">
        <?php if ( '' !== $title ) : ?>
            <h2 class="enjoy-empty__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( '' !== $text ) : ?>
            <p class="enjoy-empty__text"><?php echo esc_html( $text ); ?></p>
        <?php endif; ?>

        <?php if ( $form ) : ?>
            <div class="enjoy-empty__search jeg_archive_search" role="search" aria-label="<?php esc_attr_e( 'Search the guides', 'jnews-child' ); ?>">
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>

        <?php enjoy_empty_state_list( $found['posts'], $heading ); ?>
    </div>
    <?php
}
