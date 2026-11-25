<?php
get_header();
$notfound = new \JNews\Archive\NotFoundArchive();
?>

    <div class="jeg_main <?php $notfound->main_class(); ?>">
        <div class="jeg_container">
            <div class="jeg_content" role="main">

                <div class="jeg_section">
                    <div class="container">
                        <div class="jeg_404_content row">
                            <div class="jeg_main_content col-sm-<?php echo esc_attr($notfound->get_content_width()); ?>">

                                <div class="jeg_inner_content">
                                    <div class="jeg_archive_header">

                                        <?php if(jnews_can_render_breadcrumb()) : ?>
                                        <div class="jeg_breadcrumbs jeg_breadcrumb_container">
                                            <?php echo jnews_sanitize_output( $notfound->render_breadcrumb() ); ?>
                                        </div>
                                        <?php endif; ?>

                                        <h1 class="jeg_archive_title"><?php jnews_print_translation('Page Not Found', 'jnews', 'page_not_found'); ?></h1>
                                        <p><?php esc_html_e( 'This page does not exist or has moved. Search the guides, or start with one of the latest below.', 'jnews-child' ); ?></p>

                                        <p class="enjoy-404-home"><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back Home', 'jnews-child' ); ?></a></p>

                                        <div class="jeg_archive_search">
                                            <?php get_search_form(true); ?>
                                        </div>
                                    </div>
                                    <!-- search end -->

                                    <div class="jnews_404_content_wrapper">
                                        <?php
                                        if ( function_exists( 'enjoy_render_empty_state' ) ) {
                                            enjoy_render_empty_state( '404' );
                                        } else {
                                            echo jnews_sanitize_output( $notfound->render_content() );
                                        }
                                        ?>
                                    </div>
                                </div>

                            </div>
                            <?php $notfound->render_sidebar(); ?>
                        </div>
                    </div>
                </div>

            </div>
            <?php do_action('jnews_after_main'); ?>
        </div>
    </div>

<?php get_footer(); ?>
