<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin activation
 * Creates required database tables
 */
function mp_plugin_activate() {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // required for dbDelta()



    /*---------------------------------------------TABLE 1 : TEMP YACHT LIST-----------------*/

    $table = $wpdb->prefix . 'temp_yachts';

    $sql = "CREATE TABLE $table (

        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,

        name VARCHAR(255) NOT NULL,
        code VARCHAR(100) NOT NULL,

        price DECIMAL(12,2) DEFAULT NULL,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id),

        UNIQUE KEY code (code)

    ) $charset_collate;";

    dbDelta($sql);



    /*-------------------------------------------------------------- TABLE 2 : YACHT DETAILS */

    $table = $wpdb->prefix . 'temp_yacht_details';

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        yacht_code VARCHAR(100) NOT NULL,

        /* BASIC INFO */
        name VARCHAR(255) NOT NULL,
        previous_name VARCHAR(255) DEFAULT NULL,
        type VARCHAR(100) DEFAULT NULL,

        /* DIMENSIONS */
        length_feet DECIMAL(6,2) DEFAULT NULL,
        length_meter DECIMAL(6,2) DEFAULT NULL,
        beam DECIMAL(6,2) DEFAULT NULL,
        draft DECIMAL(6,2) DEFAULT NULL,

        /* CAPACITY */
        pax TINYINT DEFAULT NULL,
        cabins INT DEFAULT NULL,

        /* BUILD */
        year_built YEAR DEFAULT NULL,
        refit_year YEAR DEFAULT NULL,
        builder VARCHAR(255) DEFAULT NULL,

        /* PRICING */
        low_price DECIMAL(12,2) DEFAULT NULL,
        high_price DECIMAL(12,2) DEFAULT NULL,
        currency VARCHAR(10) DEFAULT NULL,
        terms VARCHAR(50) DEFAULT NULL,

        /* LOCATION */
        summer_area VARCHAR(255) DEFAULT NULL,
        winter_area VARCHAR(255) DEFAULT NULL,
        home_port VARCHAR(255) DEFAULT NULL,

        /* SPEED */
        cruise_speed VARCHAR(50) DEFAULT NULL,
        max_speed VARCHAR(50) DEFAULT NULL,

        /* CONTENT */

        description LONGTEXT,
        accommodations TEXT,

        /* CREW */
        captain_name VARCHAR(255) DEFAULT NULL,
        crew_profile LONGTEXT,

        /* IMAGES */
        main_image TEXT,
        main_image_api VARCHAR(500) DEFAULT NULL,
        layout_image TEXT,
        layout_image_api VARCHAR(500) DEFAULT NULL,
        gallery_images LONGTEXT,

        /* SYNC CONTROL */
        last_seen INT DEFAULT NULL,
        status VARCHAR(20) DEFAULT NULL,
        data_hash VARCHAR(32) DEFAULT NULL,

        /* TIMESTAMPS */
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY (id),
        UNIQUE KEY yacht_code (yacht_code),
        KEY status_last_seen (status,last_seen)
    ) $charset_collate;";
    dbDelta($sql);






    /*-------------------------------------------------------------- TABLE 3 : YACHT AVAILABILITY */

$table = $wpdb->prefix . 'yacht_availability';

$sql = "CREATE TABLE $table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    yacht_code VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    book_type TINYINT NOT NULL,
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY yacht_code (yacht_code),
    KEY date_range (start_date, end_date)
) $charset_collate;";

dbDelta($sql);


//CALIING PAGE FUNCTIOPNS
create_yachts_page();
create_single_yachts_page();
}
function create_yachts_page() {
    $existing = get_page_by_path('all-yachts');
    if ($existing) return;

    $page = array(
        'post_title'   => 'All Yachts',
        'post_name'    => 'all-yachts',
        'post_content' => '[all_yachtes]',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    wp_insert_post($page);
}

function create_single_yachts_page() {
    $existing = get_page_by_path('single-yacht-page');
    if ($existing) return;

    $page = array(
        'post_title'   => 'Single Yacht Page',
        'post_name'    => 'single-yacht-page',
        'post_content' => '[yacht_single]',
        'post_status'  => 'publish',
        'post_type'    => 'page'
    );
    wp_insert_post($page);
}










