<?php
/**
 * Plugin Name: Yacht
 * Description: Basic WordPress plugin with Bootstrap and AJAX.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}


define('plugin_path', plugin_dir_path(__FILE__)); //php fioles
define('plugin_url', plugin_dir_url(__FILE__));  //assets files


// FILES 
require_once plugin_path . 'activation.php';
// require_once plugin_path . 'yacht_location_server_cron_job.php';
require_once plugin_path . 'home_page.php';

require_once plugin_path . 'yacht-detail-sync/yacht_sync_processor.php';
// require_once plugin_path . 'yacht-detail-sync/yacht_sync_batch.php';
require_once plugin_path . 'yacht-detail-sync/yacht_sync_api.php';
require_once plugin_path . 'yacht-detail-sync/yacht_sync_image.php';

// ARCHIVE TEMPLATE FILES
require_once plugin_path . 'yacht-archive/yacht_archive.php';
require_once plugin_path . 'yacht-archive/yacht_archive_sidebar.php';
require_once plugin_path . 'yacht-archive/yacht_sorting.php';

// SINGLE YACHT  FILES
require_once plugin_path . 'yacht-single/single_yacht.php';
// Load admin dashboard file for sync monitoring
require_once plugin_path . 'admin/yacht_admin_dashboard.php';




register_activation_hook(__FILE__, 'mp_plugin_activate');



/* Loads bootstrap CSS and JS */

function mp_enqueue_assets() {

   /* Bootstrap CSS */
        // wp_enqueue_style(
        // 'mp-bootstrap-css',
        // 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        // array(),
        // '5.3.3'
        // );

        /* Plugin Style */
  /* Plugin Style */
wp_enqueue_style(
    'mp-style',
    plugin_url . 'style.css',
    array(),
    '1.0'
);



}

add_action('wp_enqueue_scripts', 'mp_enqueue_assets');
add_action('admin_enqueue_scripts', 'mp_enqueue_assets');



/*
| Handles both logged-in and guest requests
*/

function mp_ajax_handler() {

    /* Security check */
    check_ajax_referer('mp_ajax_nonce', 'nonce');

    /* Example response */
    wp_send_json_success(array(
        'message' => 'AJAX request successful'
    ));

}

add_action('wp_ajax_mp_ajax_handler', 'mp_ajax_handler');
add_action('wp_ajax_nopriv_mp_ajax_handler', 'mp_ajax_handler');


// CHANGING IMAGE FORMATE TO WEBP
// Force WordPress to generate WebP images instead of JPG/PNG
add_filter('image_editor_output_format', function ($formats) {

    // Convert JPEG uploads to WebP
    $formats['image/jpeg'] = 'image/webp';

    // Convert PNG uploads to WebP
    $formats['image/png']  = 'image/webp';

    return $formats;

});







//YACHT SORTING FOR ARCVHIVE PAGE
add_action('wp_ajax_yacht_sorting','yacht_sorting');
add_action('wp_ajax_nopriv_yacht_sorting','yacht_sorting');


function yacht_archive_scripts(){

wp_enqueue_script(
'yacht-archive-js',
plugin_dir_url(__FILE__) . 'yacht-archive/yacht_archive.js',
array('jquery'),
null,
true
);

wp_localize_script(
'yacht-archive-js',
'ajax_object',
array('ajax_url'=>admin_url('admin-ajax.php'))
);

}

add_action('wp_enqueue_scripts','yacht_archive_scripts');


// FILTER BY GUESTS
add_action('wp_ajax_filter_yachts','filter_yachts');
add_action('wp_ajax_nopriv_filter_yachts','filter_yachts');