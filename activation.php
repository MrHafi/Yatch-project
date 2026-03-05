<?php

if (!defined('ABSPATH')) {
    exit;
}

function mp_plugin_activate() {

    global $wpdb;

    $table = $wpdb->prefix . 'temp_yachts';
    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // for dbDelta 

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



/* run sync after table creation */
// yacht_location_sync(); ONLY FIRST TIME





//-------------------- TABLE FOR YACHT-----------
$table = $wpdb->prefix . 'temp_yacht_details';
$sql = "CREATE TABLE $table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    yacht_code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) DEFAULT NULL,

    length_feet DECIMAL(6,2) DEFAULT NULL,
    beam DECIMAL(6,2) DEFAULT NULL,
    draft DECIMAL(6,2) DEFAULT NULL,

    pax TINYINT DEFAULT NULL,
    cabins INT DEFAULT NULL,

    year_built YEAR DEFAULT NULL,
    builder VARCHAR(255) DEFAULT NULL,

    low_price DECIMAL(12,2) DEFAULT NULL,
    high_price DECIMAL(12,2) DEFAULT NULL,
    currency VARCHAR(10) DEFAULT NULL,

    summer_area VARCHAR(255) DEFAULT NULL,
    winter_area VARCHAR(255) DEFAULT NULL,
    home_port VARCHAR(255) DEFAULT NULL,

    cruise_speed VARCHAR(50) DEFAULT NULL,
    ac VARCHAR(50) DEFAULT NULL,

    accommodations TEXT,
    description LONGTEXT,

    captain_name VARCHAR(255) DEFAULT NULL,
    crew_name VARCHAR(255) DEFAULT NULL,
    crew_profile LONGTEXT,

    main_image TEXT,
    layout_image TEXT,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY yacht_code (yacht_code)

) $charset_collate;";

dbDelta($sql);


// yacht_details_sync_batch();
} //CLOSING ACTIVATION 





