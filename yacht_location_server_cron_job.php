<?php

// /* Load WordPress so we can use $wpdb and WP functions 
// require_once ABSPATH . 'wp-load.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

if (!defined('ABSPATH')) {
    exit;
}

//  Function to sync yachts from API into DB 
function yacht_location_sync() {

    global $wpdb;
    $table = $wpdb->prefix . 'temp_yachts';

    $url = 'https://www.centralyachtagent.com/snapins/json-snyachts.php?user=1073&apicode=1073YF4$sdRr91%X&ylocations=src7';

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return;
    }

    $body = wp_remote_retrieve_body($response); //gettingt he body content

    /* Convert JSON to array */
    $data = json_decode($body, true);

    if (!isset($data['yacht']) || empty($data['yacht'])) {
    return;
}

    foreach ($data['yacht'] as $yacht) {

        /* Prepare values */
        $name  = sanitize_text_field($yacht['yachtName']);
        $code  = sanitize_text_field($yacht['yachtId']);
        $price = floatval($yacht['yachtLowNumericPrice']);

        /* Insert new row or update existing one */
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table (name, code, price)
                VALUES (%s, %s, %d)
                ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                price = VALUES(price)",
                $name,
                $code,
                $price
            )
        );
    }

}

/* Run the sync when cron hits this file */
yacht_location_sync();