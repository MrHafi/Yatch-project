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
require_once plugin_path . 'yacht_location_server_cron_job.php';
require_once plugin_path . 'yacht_detail_server_cron_job.php';



register_activation_hook(__FILE__, 'mp_plugin_activate');



/* Loads bootstrap CSS and JS */

function mp_enqueue_assets() {

    /* Bootstrap CSS */
    wp_enqueue_style(
        'mp-bootstrap-css',
        plugin_url . 'assets/css/bootstrap.min.css',
        array(),
        '5.3'
    );

    /* Bootstrap JS */
    wp_enqueue_script(
        'mp-bootstrap-js',
        plugin_url . 'assets/js/bootstrap.bundle.min.js',
        array('jquery'),
        '5.3',
        true
    );

    /*
    |--------------------------------------------------------------
    | Pass AJAX data to JavaScript
    |--------------------------------------------------------------
    */

    wp_localize_script(
        'mp-bootstrap-js',
        'mp_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('mp_ajax_nonce')
        )
    );
}

add_action('wp_enqueue_scripts', 'mp_enqueue_assets');
add_action('admin_enqueue_scripts', 'mp_enqueue_assets');



/*
|--------------------------------------------------------------------------
| AJAX Handler
|--------------------------------------------------------------------------
| Handles both logged-in and guest requests
|
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