<?php
/**
 * One-off: navigation restructure + quick fixes (2026-11-25 design review).
 *
 * Run with: wp eval-file nav-restructure-20261125.php
 * Idempotent: safe to run twice. Take a DB backup first.
 *
 * @package jnews-child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$enjoy_menu_id = 159;

// 1. Category assignments so posts appear under the section readers expect.
$enjoy_add = array(
    3344 => array( 257, 225 ), // Split ferry port -> Getting Around, Split.
    3345 => array( 257 ),      // Ferry booking -> Getting Around.
    3320 => array( 257 ),      // Renting a car -> Getting Around.
    3105 => array( 225, 245 ), // Zagreb-Split train -> Split, Zagreb.
    2820 => array( 392 ),      // Tap water -> Stay Safe.
    2791 => array( 392 ),      // Heatwave -> Stay Safe.
    2785 => array( 392 ),      // Emergency numbers -> Stay Safe.
    2778 => array( 392 ),      // Snakes -> Stay Safe.
);
foreach ( $enjoy_add as $enjoy_post_id => $enjoy_terms ) {
    if ( get_post( $enjoy_post_id ) ) {
        wp_set_post_terms( $enjoy_post_id, $enjoy_terms, 'category', true );
    }
}
// Merge duplicate "Getting Around Croatia" (463) into "Getting Around" (257).
foreach ( array( 3344, 3345 ) as $enjoy_post_id ) {
    wp_remove_object_terms( $enjoy_post_id, 463, 'category' );
}

// 2. Main menu: delete items for empty or redundant sections.
$enjoy_delete = array(
    404,                                            // Home (logo links home).
    1884, 1885, 1869, 1870, 1867, 1871,             // Empty Dalmatia children.
    1886, 1887, 1888, 1874, 1873,                   // Istria children (same 1-2 posts).
    1875, 1889, 1877, 1876, 1890,                   // Kvarner tree.
    1897, 1898, 1899,                               // Zagreb children, Slavonia.
    1893, 1896,                                     // Kornati, Other Parks.
    1906,                                           // Budget & Costs.
    1908, 1911, 1909, 1912, 1913, 1914, 1910,       // Things to Do tree.
    1916, 1926, 1927, 1917, 1918,                   // Food & Drink children.
    1925, 1919, 1924, 1920, 1921, 1923,             // Travel Tips tree (Money kept).
    1928, 1931, 1929, 1930, 1933, 1934,             // Inspiration tree.
    1957,                                           // Blog & News.
);

// Final structure: db_id => array( parent, position, label or null ).
$enjoy_keep = array(
    1865 => array( 0, 1, null ),              // Destinations.
    1866 => array( 1865, 2, null ),           // Dalmatia.
    1883 => array( 1866, 3, null ),           // Split.
    1868 => array( 1866, 4, null ),           // Dubrovnik.
    1872 => array( 1865, 5, null ),           // Istria.
    1891 => array( 1865, 6, 'Zagreb' ),       // Zagreb & Central Croatia.
    1892 => array( 1865, 7, null ),           // National Parks.
    1894 => array( 1892, 9, null ),           // Krka.
    1895 => array( 1892, 10, null ),          // Mljet.
    1905 => array( 0, 11, null ),             // Getting Around.
    1902 => array( 0, 12, 'When to Go' ),     // When to Visit.
    1900 => array( 0, 13, 'Plan Your Trip' ), // Trip Planning.
    1904 => array( 1900, 14, null ),          // Itineraries.
    1901 => array( 1900, 15, null ),          // Visas & Entry.
    1903 => array( 1900, 16, null ),          // Where to Stay.
    1922 => array( 1900, 17, null ),          // Money & Payments.
    1915 => array( 0, 19, null ),             // Food & Drink.
);

foreach ( $enjoy_keep as $enjoy_id => $enjoy_cfg ) {
    if ( 'nav_menu_item' !== get_post_type( $enjoy_id ) ) {
        WP_CLI::warning( "Menu item $enjoy_id missing, skipped." );
        continue;
    }
    update_post_meta( $enjoy_id, '_menu_item_menu_item_parent', (string) $enjoy_cfg[0] );
    // Plain dropdowns: one tree for desktop and mobile instead of JNews mega menus.
    delete_post_meta( $enjoy_id, 'menu_item_jnews_mega_menu' );
    $enjoy_update = array(
        'ID'         => $enjoy_id,
        'menu_order' => $enjoy_cfg[1],
    );
    if ( null !== $enjoy_cfg[2] ) {
        $enjoy_update['post_title'] = $enjoy_cfg[2];
    }
    wp_update_post( $enjoy_update );
}

foreach ( $enjoy_delete as $enjoy_id ) {
    if ( 'nav_menu_item' === get_post_type( $enjoy_id ) ) {
        wp_delete_post( $enjoy_id, true );
    }
}

// New items: Plitvice Lakes under National Parks, Stay Safe at top level.
$enjoy_new = array(
    249 => array( 1892, 8, '' ),
    392 => array( 0, 18, 'Stay Safe' ),
);
$enjoy_existing = wp_list_pluck( (array) wp_get_nav_menu_items( $enjoy_menu_id ), 'object_id' );
foreach ( $enjoy_new as $enjoy_term_id => $enjoy_cfg ) {
    if ( ! term_exists( $enjoy_term_id, 'category' ) || in_array( (string) $enjoy_term_id, array_map( 'strval', $enjoy_existing ), true ) ) {
        continue;
    }
    wp_update_nav_menu_item(
        $enjoy_menu_id,
        0,
        array(
            'menu-item-object-id' => $enjoy_term_id,
            'menu-item-object'    => 'category',
            'menu-item-type'      => 'taxonomy',
            'menu-item-parent-id' => $enjoy_cfg[0],
            'menu-item-position'  => $enjoy_cfg[1],
            'menu-item-title'     => $enjoy_cfg[2],
            'menu-item-status'    => 'publish',
        )
    );
}

// 3. Footer menu: remove dead "#" links, add Contact.
foreach ( array( 415, 416, 417 ) as $enjoy_id ) {
    if ( 'nav_menu_item' === get_post_type( $enjoy_id ) && '#' === get_post_meta( $enjoy_id, '_menu_item_url', true ) ) {
        wp_delete_post( $enjoy_id, true );
    }
}
$enjoy_footer = wp_list_pluck( (array) wp_get_nav_menu_items( 160 ), 'object_id' );
if ( get_post( 276 ) && ! in_array( '276', array_map( 'strval', $enjoy_footer ), true ) ) {
    wp_update_nav_menu_item(
        160,
        0,
        array(
            'menu-item-object-id' => 276,
            'menu-item-object'    => 'page',
            'menu-item-type'      => 'post_type',
            'menu-item-title'     => 'Contact',
            'menu-item-status'    => 'publish',
        )
    );
}

// 4. Theme options: no "Continue Reading" gate on mobile; show the post date.
set_theme_mod( 'jnews_mobile_truncate', false );
set_theme_mod( 'jnews_single_show_post_date', true );

// 5. Rank Math: index category archives (empty ones stay noindex), plain titles.
$enjoy_titles = get_option( 'rank-math-options-titles' );
if ( is_array( $enjoy_titles ) ) {
    $enjoy_titles['tax_category_robots'] = array( 'index' );
    $enjoy_titles['tax_category_title']  = '%term% %page% %sep% %sitename%';
    update_option( 'rank-math-options-titles', $enjoy_titles );
}

// 6. Homepage: replace leftover theme demo tagline.
$enjoy_old  = 'Find your best tour and start the adventure at the lowest budget and best experiences.';
$enjoy_new  = 'Practical, checked guides to getting around, staying safe and planning your time in Croatia.';
$enjoy_data = get_post_meta( 398, '_elementor_data', true );
if ( is_string( $enjoy_data ) && false !== strpos( $enjoy_data, $enjoy_old ) ) {
    update_post_meta( 398, '_elementor_data', wp_slash( str_replace( $enjoy_old, $enjoy_new, $enjoy_data ) ) );
    delete_post_meta( 398, '_elementor_element_cache' );
}

WP_CLI::success( 'Navigation restructure applied.' );
