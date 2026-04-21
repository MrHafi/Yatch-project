<?php
// RUN THIS FILE VIA SERVER CRON JOB 
// /* Load WordPress so we can use $wpdb and WP functions 
// require_once ABSPATH . 'wp-load.php';
if (php_sapi_name() !== 'cli') {
    exit('Direct access not allowed.');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

if (!defined('ABSPATH')) {
    exit;
}

//  Function to sync yachts from API into DB 
function yacht_location_sync() {

    // Save the time this location sync started so dashboard knows when it last ran
    update_option('yacht_location_last_run', current_time('mysql'));

    // Initialize counters to track inserted and updated yachts
    $inserted = 0;
    $updated  = 0;

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

        // affected_rows is 1 for insert, 2 for update in ON DUPLICATE KEY queries
        if ( $wpdb->rows_affected === 1 ) $inserted++;
        if ( $wpdb->rows_affected === 2 ) $updated++;
    }

    // Save location sync stats to WP options so dashboard can display them
    update_option('yacht_location_sync_stats', [
        'inserted' => $inserted,
        'updated'  => $updated,
        'time'     => current_time('mysql'),
    ]);

    // Append this sync result to the activity log, keeping only last 50 entries
    $log = get_option('yacht_sync_activity_log', []);
    $log[] = current_time('mysql') . " - Location sync: {$inserted} inserted, {$updated} updated";
    update_option('yacht_sync_activity_log', array_slice($log, -50));

}

/* Run the sync when cron hits this file */
yacht_location_sync();