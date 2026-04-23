<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('yacht_single', 'yacht_single_page');

function yacht_single_page() {

    // get yacht code from URL
    $code = isset($_GET['usr_id_in']) ? sanitize_text_field($_GET['usr_id_in']) : '';

    // if no code in URL — show error
    if (empty($code)) {
        return '<p>No yacht selected.</p>';
    }

    global $wpdb;
    $table = $wpdb->prefix . 'temp_yacht_details';

    // get YACHT DETAILS  from DB
    $yacht = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE yacht_code = %s AND status = 'active' LIMIT 1",
        $code
    ));

    // if yacht not found
    if (!$yacht) {
        return '<p>Yacht not found.</p>';
    }

    // decode gallery images
    $yacht->gallery = json_decode($yacht->gallery_images, true);
    if (!is_array($yacht->gallery)) {
        $yacht->gallery = [];
    }

ob_start();

include plugin_path . 'yacht-single/templates/hero.php';

?>

<!-- STICKY NAV -->
<div class="yacht-sticky-nav">
    <div class="yacht-sticky-inner">
        <a href="#overview">Overview</a>
        <a href="#specifications">Specifications</a>
        <a href="#gallery">Gallery</a>
        <a href="#crew">Crew</a>
    </div>
</div>

<?php
include plugin_path . 'yacht-single/templates/overview.php';
include plugin_path . 'yacht-single/templates/specs.php';
include plugin_path . 'yacht-single/templates/gallery.php';
include plugin_path . 'yacht-single/templates/crew.php';





return ob_get_clean();
}


// create single yacht page on activation
function yacht_create_single_page() {

    $existing = get_page_by_path('single-yacht-page');
    if ($existing) return;

    wp_insert_post([
        'post_title'   => 'Single Yacht Page',
        'post_name'    => 'single-yacht-page',
        'post_content' => '[yacht_single]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);
}

register_activation_hook(
    plugin_dir_path(__FILE__) . '../yacht.php',
    'yacht_create_single_page'
);