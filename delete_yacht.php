<?php
// RUN THIS FILE VIA SERVER CRON JOB - runs daily but only deletes yachts removed 7+ days ago

if (php_sapi_name() !== 'cli') {
    exit('Direct access not allowed.');
}
set_time_limit(0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if (!defined('ABSPATH')) {
    exit;
}

// ---------------- CONFIG ----------------
$days_before_delete = 7; // REMOVE AFTER 7 DAYS OF INACTIVE/ REMOVE OFG YACHT

// ---------------- LOG FUNCTION ----------------
function yacht_delete_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'yacht_delete_log.txt';
    $time     = current_time('mysql');
    file_put_contents($log_file, $time . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

// ---------------- MAIN DELETE FUNCTION ----------------
function yacht_cleanup_removed($days_before_delete) {

    global $wpdb;

    $details_table      = $wpdb->prefix . 'temp_yacht_details';
    $yachts_table       = $wpdb->prefix . 'temp_yachts';
    $availability_table = $wpdb->prefix . 'yacht_availability';

    // ---------------- STEP 1: Find yachts to delete ----------------
    $yachts = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $details_table
         WHERE status = 'removed'
         AND updated_at < NOW() - INTERVAL %d DAY",
        $days_before_delete
    ));

    if (empty($yachts)) {
        yacht_delete_log('No yachts to delete today.');
        return;
    }

    yacht_delete_log('Found ' . count($yachts) . ' yacht(s) to delete.');

    $total_deleted = 0;

    foreach ($yachts as $yacht) {

        $code = $yacht->yacht_code;

        yacht_delete_log('Starting deletion for yacht: ' . $code);

        // ---------------- STEP 2A: Delete main image WEBP MAIN IMAGE ----------------
        if (!empty($yacht->main_image)) {
            $attachment_id = attachment_url_to_postid($yacht->main_image);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true);
                yacht_delete_log('Deleted main image for yacht: ' . $code);
            }
        }

        // ---------------- STEP 2B: Delete layout image WEBP FLOORPLAN ----------------
        if (!empty($yacht->layout_image)) {
            $attachment_id = attachment_url_to_postid($yacht->layout_image);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true);
                yacht_delete_log('Deleted layout image for yacht: ' . $code);
            }
        }

        // ---------------- STEP 2C: Delete gallery images API  ----------------
        if (!empty($yacht->gallery_images)) {

            $gallery = json_decode($yacht->gallery_images, true);

            if (!empty($gallery) && is_array($gallery)) {
                foreach ($gallery as $image_url) {
                    if (empty($image_url)) continue;

                    $attachment_id = attachment_url_to_postid($image_url);
                    if ($attachment_id) {
                        wp_delete_attachment($attachment_id, true);
                    }
                }
                yacht_delete_log('Deleted gallery images for yacht: ' . $code);
            }
        }

        // ---------------- STEP 2D: Delete availability records ----------------
        $wpdb->delete(
            $availability_table,
            ['yacht_code' => $code],
            ['%s']
        );
        yacht_delete_log('Deleted availability records for yacht: ' . $code);

        // ---------------- STEP 2E: Delete from temp_yacht_details ----------------
        $wpdb->delete(
            $details_table,
            ['yacht_code' => $code],
            ['%s']
        );
        yacht_delete_log('Deleted details row for yacht: ' . $code);

        // ---------------- STEP 2F: Delete from temp_yachts ----------------
        $wpdb->delete(
            $yachts_table,
            ['code' => $code],
            ['%s']
        );
        yacht_delete_log('Deleted from temp_yachts for yacht: ' . $code);

        $total_deleted++;
    }

    // ---------------- STEP 3: Final log ----------------
    yacht_delete_log('Cleanup complete. Total yachts deleted: ' . $total_deleted);

    // ---------------- STEP 4: Save to activity log ----------------
    $log = get_option('yacht_sync_activity_log', []);
    $log[] = current_time('mysql') . ' - Cleanup: ' . $total_deleted . ' yacht(s) permanently deleted.';
    update_option('yacht_sync_activity_log', array_slice($log, -50));
}

// ---------------- RUN ----------------
yacht_cleanup_removed($days_before_delete);