<?php

// RUN THIS FILE VIA SERVER CRON JOB 
if (php_sapi_name() !== 'cli') {
    exit('Direct access not allowed.');
}

set_time_limit(0); // Prevent timeout during large sync batches.

// Load WordPress core.
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
    require_once ABSPATH . 'wp-includes/pluggable.php';

global $wp_rewrite;

// Make sure rewrite is available when file runs directly.
if (empty($wp_rewrite)) {
    $wp_rewrite = new WP_Rewrite();
    $wp_rewrite->init();
}

// Load required sync files.
require_once plugin_dir_path(__FILE__) . '/yacht_sync_processor.php';
require_once plugin_dir_path(__FILE__) . '/yacht_sync_api.php';
require_once plugin_dir_path(__FILE__) . '/yacht_sync_image.php';


// ---------------- Write sync log func ----------------
function yacht_sync_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'yacht_detail_sync_log.txt';
    $time = current_time('mysql');
    file_put_contents($log_file, $time . ' - ' . $message . PHP_EOL, FILE_APPEND);
}


// ---------------- Log return function ----------------//
function yacht_details_sync_batch() {

    global $wpdb;

    $limit = 50; // Number of yachts to process per batch.

    $yachts_table  = $wpdb->prefix . 'temp_yachts';
    $details_table = $wpdb->prefix . 'temp_yacht_details';

    // PREZVENT DOUBLE RUN
    if (get_transient('yacht_sync_lock')) {
        yacht_sync_log('Sync skipped because lock already exists.');
        return;
    }

    // Create lock for this batch.
    set_transient('yacht_sync_lock', 1, 600);

    // Save the time this batch started so the dashboard knows when sync last ran
    update_option('yacht_last_sync_started', current_time('mysql'));
    yacht_sync_log('Sync batch started.');

    // Load media functions only when sync runs.
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // ---------------- Get one shared sync timestamp ----------------
    $sync_time = get_option('yacht_sync_time');

    if (!$sync_time) {
        $sync_time = current_time('timestamp'); // Keep same time for full sync cycle.
        update_option('yacht_sync_time', $sync_time);
    }

    // ---------------- Get current offset ----------------
    $offset = (int) get_option('yacht_details_sync_offset', 0);

    // ---------------- Load next batch of yacht codes ----------------
    $codes = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT code FROM $yachts_table ORDER BY code LIMIT %d OFFSET %d",
            $limit,
            $offset
        )
    );

    // ---------------- Finish sync when no codes remain ----------------
    if (empty($codes)) {

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $details_table SET status = 'removed' WHERE last_seen < %d",
                $sync_time
            )
        );

     update_option('yacht_details_sync_offset', 0); // Reset for next full cycle.
        delete_option('yacht_sync_time'); // Remove old cycle timestamp.
        delete_transient('yacht_sync_lock'); // Release lock.

        // Save the time this full sync cycle finished for the dashboard
        update_option('yacht_last_sync_finished', current_time('mysql'));

        // DELETING TRANSIENT
        delete_transient('yacht_initial_results');
        delete_transient('yacht_sidebar_guests');
        delete_transient('yacht_sidebar_locations');

        // Log cycle finish into the activity log
        $log = get_option('yacht_sync_activity_log', []);
        $log[] = current_time('mysql') . ' - Full sync cycle finished.';
        update_option('yacht_sync_activity_log', array_slice($log, -50));

        yacht_sync_log('Sync cycle finished.');
        return;
    }

    // ---------------- Process each yacht ----------------
    $added   = 0;
    $updated = 0;
    $skipped = 0;
    foreach ($codes as $code) {

        if (empty($code)) {
            continue; // Skip blank codes.
        }

        // Capture status returned by processor to count results
        $status = yacht_process_single_yacht($code, $details_table, $sync_time);

        // Increment the correct counter based on what happened to this yacht
        if ( $status === 'added' )        $added++;
        elseif ( $status === 'updated' )  $updated++;
        elseif ( $status === 'skipped' )  $skipped++;
    }

    // ---------------- Save next offset ----------------
    update_option('yacht_details_sync_offset', $offset + $limit);

  delete_transient('yacht_sync_lock'); // Release lock after batch completes.

    // Save per-batch stats to WP options so the dashboard can display them
    update_option('yacht_sync_last_batch_stats', [
        'added'   => $added,
        'updated' => $updated,
        'skipped' => $skipped,
        'time'    => current_time('mysql'),
    ]);

    // Append this batch result to the activity log, keeping only last 50 entries
    $log = get_option('yacht_sync_activity_log', []);
    $log[] = current_time('mysql') . " - Batch done: {$added} added, {$updated} updated, {$skipped} skipped";
    update_option('yacht_sync_activity_log', array_slice($log, -50));

    yacht_sync_log('Sync batch completed.');
}


// ---------------- Run batch sync ----------------
yacht_details_sync_batch();