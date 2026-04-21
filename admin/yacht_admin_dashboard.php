<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register the admin menu 
add_action('admin_menu', 'yacht_admin_menu');

function yacht_admin_menu() {
    add_menu_page(
        'Yacht Sync Dashboard',
        'Yacht Sync',
        'manage_options',
        'yacht-sync-dashboard',
        'yacht_admin_dashboard_page',
        'dashicons-admin-plugins',
        30
    );
}


function yacht_admin_dashboard_page() {

    // Fetch all saved options from WP options table
    $last_started  = get_option('yacht_last_sync_started', 'Never');
    $last_finished = get_option('yacht_last_sync_finished', 'Never');
    $location_last = get_option('yacht_location_last_run', 'Never');
    $batch_stats   = get_option('yacht_sync_last_batch_stats', []);
    $location_stats = get_option('yacht_location_sync_stats', []);
    $activity_log  = get_option('yacht_sync_activity_log', []);

    // Fetch yacht counts directly from the database oj page load foer real data
    global $wpdb;
    $details_table = $wpdb->prefix . 'temp_yacht_details';
    $total_active  = $wpdb->get_var("SELECT COUNT(*) FROM $details_table WHERE status = 'active'");
    $total_removed = $wpdb->get_var("SELECT COUNT(*) FROM $details_table WHERE status = 'removed'");
    $total_all     = $wpdb->get_var("SELECT COUNT(*) FROM $details_table");

    // Read last 30 lines from the sync log file
    $log_file = plugin_dir_path(__FILE__) . '../yacht-detail-sync/yacht_detail_sync_log.txt';
    $log_lines = [];
    if (file_exists($log_file)) {
        $all_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $log_lines = array_slice($all_lines, -30);
        $log_lines = array_reverse($log_lines); // Show newest first
    }

    ?>

    <div class="wrap">
        <h1>🚢 Yacht Sync Dashboard</h1>

        <!-- YACHT COUNTS -->
        <h2>Database Overview</h2>
        <table class="widefat" style="max-width:500px;">
            <tr><td><strong>Total Yachts</strong></td><td><?php echo intval($total_all); ?></td></tr>
            <tr><td><strong>Active Yachts</strong></td><td style="color:green;"><?php echo intval($total_active); ?></td></tr>
            <tr><td><strong>Removed Yachts</strong></td><td style="color:red;"><?php echo intval($total_removed); ?></td></tr>
        </table>

        <!-- DETAIL SYNC STATS -->
        <h2 style="margin-top:30px;">Detail Sync (yacht_sync_batch)</h2>
        <table class="widefat" style="max-width:500px;">
            <tr><td><strong>Last Batch Started</strong></td><td><?php echo esc_html($last_started); ?></td></tr>
            <tr><td><strong>Last Cycle Finished</strong></td><td><?php echo esc_html($last_finished); ?></td></tr>
            <?php if (!empty($batch_stats)) : ?>
            <tr><td><strong>Last Batch Added</strong></td><td style="color:green;"><?php echo intval($batch_stats['added']); ?></td></tr>
            <tr><td><strong>Last Batch Updated</strong></td><td style="color:orange;"><?php echo intval($batch_stats['updated']); ?></td></tr>
            <tr><td><strong>Last Batch Skipped</strong></td><td><?php echo intval($batch_stats['skipped']); ?></td></tr>
            <tr><td><strong>Last Batch Time</strong></td><td><?php echo esc_html($batch_stats['time']); ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- LOCATION SYNC STATS -->
        <h2 style="margin-top:30px;">Location Sync (yacht_location_server_cron_job)</h2>
        <table class="widefat" style="max-width:500px;">
            <tr><td><strong>Last Run</strong></td><td><?php echo esc_html($location_last); ?></td></tr>
            <?php if (!empty($location_stats)) : ?>
            <tr><td><strong>Last Inserted</strong></td><td style="color:green;"><?php echo intval($location_stats['inserted']); ?></td></tr>
            <tr><td><strong>Last Updated</strong></td><td style="color:orange;"><?php echo intval($location_stats['updated']); ?></td></tr>
            <tr><td><strong>Last Run Time</strong></td><td><?php echo esc_html($location_stats['time']); ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- ACTIVITY LOG -->
        <h2 style="margin-top:30px;">Activity Log (last 50 entries)</h2>
        <?php if (!empty($activity_log)) : ?>
        <ul style="background:#f9f9f9;padding:15px;max-width:800px;max-height:300px;overflow-y:scroll;border:1px solid #ddd;">
            <?php foreach (array_reverse($activity_log) as $entry) : ?>
                <li style="padding:4px 0;border-bottom:1px solid #eee;"><?php echo esc_html($entry); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
            <p>No activity logged yet.</p>
        <?php endif; ?>

        <!-- RAW LOG FILE -->
        <h2 style="margin-top:30px;">Raw Log File (last 30 lines)</h2>
        <?php if (!empty($log_lines)) : ?>
        <ul style="background:#1e1e1e;color:#00ff00;padding:15px;max-width:800px;max-height:300px;overflow-y:scroll;font-family:monospace;font-size:12px;">
            <?php foreach ($log_lines as $line) : ?>
                <li style="padding:2px 0;"><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
            <p>Log file is empty or does not exist yet.</p>
        <?php endif; ?>

    </div>

    <?php
}