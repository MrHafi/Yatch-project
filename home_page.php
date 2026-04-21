<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


add_shortcode('yacht_home', 'yacht_home_page');

function yacht_home_page() {

    global $wpdb;
    $table = $wpdb->prefix . 'temp_yacht_details';

    // get locations for dropdown
    $locations = get_transient('yacht_sidebar_locations');
    if (!$locations) {
        $locations = $wpdb->get_col(
            "SELECT DISTINCT home_port FROM $table
             WHERE home_port IS NOT NULL AND home_port != ''
             AND status = 'active'
             ORDER BY home_port ASC"
        );
        set_transient('yacht_sidebar_locations', $locations, 12 * HOUR_IN_SECONDS);
    }

    // get guests for dropdown
    $guests = get_transient('yacht_sidebar_guests');
    if (!$guests) {
        $guests = $wpdb->get_col(
            "SELECT DISTINCT pax FROM $table
             WHERE pax IS NOT NULL
             AND status = 'active'
             ORDER BY pax ASC"
        );
        set_transient('yacht_sidebar_guests', $guests, 12 * HOUR_IN_SECONDS);
    }

    ob_start(); ?>

    <div class="yacht-hero">
        <div class="yacht-hero-inner">

            <h1>Find Your Perfect Yacht</h1>
            <p>Search from our extensive fleet of crewed charter yachts</p>

            <form class="yacht-search-form" action="<?php echo esc_url(home_url('/all-yachts/')); ?>" method="GET">

                <!-- LOCATION -->
                <div class="yacht-search-field">
                    <label>Location</label>
                    <select name="location">
                        <option value="">All Locations</option>
                        <?php foreach($locations as $loc): ?>
                            <option value="<?php echo esc_attr($loc); ?>">
                                <?php echo esc_html($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- GUESTS -->
                <div class="yacht-search-field">
                    <label>Guests</label>
                    <select name="guests">
                        <option value="">Any</option>
                        <?php foreach($guests as $guest): ?>
                            <option value="<?php echo esc_attr($guest); ?>">
                                <?php echo esc_html($guest); ?> Guests
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- CHECK IN -->
                <div class="yacht-search-field">
                    <label>Check-in</label>
                    <input type="date" name="checkin" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- CHECK OUT -->
                <div class="yacht-search-field">
                    <label>Check-out</label>
                    <input type="date" name="checkout" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- SUBMIT -->
                <div class="yacht-search-field">
                    <button type="submit" class="yacht-search-btn">Search Yachts</button>
                </div>

            </form>

        </div>
    </div>

    <?php
    return ob_get_clean();
}


// -----------------------------------------------
// CREATE PAGE ON ACTIVATION
// -----------------------------------------------
function yacht_create_home_page() {

    // check if page already exists
    $existing = get_page_by_path('home');
    if ($existing) {
        return;
    }

    $page = array(
        'post_title'   => 'Home',
        'post_name'    => 'home',
        'post_content' => '[yacht_home]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );

    $page_id = wp_insert_post($page);

    // set as front page
    if ($page_id && !is_wp_error($page_id)) {
        update_option('page_on_front', $page_id);
        update_option('show_on_front', 'page');
    }
}

register_activation_hook(
    plugin_dir_path(__FILE__) . 'yacht.php',
    'yacht_create_home_page'
);