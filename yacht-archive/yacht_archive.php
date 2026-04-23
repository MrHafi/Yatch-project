<?php
add_shortcode('all_yachtes','all_yachtes');

function all_yachtes(){ ?>

<div class="container p-5 text-center">
    <h2>Explore Our Extensive Fleet of Crewed Charter Yachts</h2>
    <p>Discover our curated selection of crewed charter yachts...</p>
</div>

<div class="all_page_template d-flex flex-row"> <!-- FLEX ROW 25% / 75% -->

    <!-- SIDEBAR  -->
    <div id="sidebar" class="flex-shrink-0" style="flex-basis:25%;">
        <?php yacht_sidebar(); ?>
    </div>

    <!-- MAIN SECTION -->
    <div class="main_section" style="flex-basis:75%;">

        <div class="container d-flex align-items-end"> <!-- SORTING -->
            <select id="yacht_sort" class="yacht_filter">
                <option value="">Default</option>
                <option value="price_low">Price (low to high)</option>
                <option value="price_high">Price (high to low)</option>
                <option value="name_az">Yacht Name (A-Z)</option>
                <option value="name_za">Yacht Name (Z-A)</option>
                <option value="guest_low">Guests (low to high)</option>
                <option value="guest_high">Guests (high to low)</option>
                <option value="size_low">Size (low to high)</option>
                <option value="size_high">Size (high to low)</option>
            </select>
        </div>

        <div class="container py-5">
            <div id="yacht_results">
                <div class="row g-4">
                    <?php
                  global $wpdb;
                    $table = $wpdb->prefix . 'temp_yacht_details';

                    // read URL params from homepage search form
                    $url_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
                    $url_guests   = isset($_GET['guests'])   ? intval($_GET['guests'])                : 0; //NO. GUESTS
                    $url_checkin  = isset($_GET['checkin'])  ? sanitize_text_field($_GET['checkin'])  : '';
                    $url_checkout = isset($_GET['checkout']) ? sanitize_text_field($_GET['checkout']) : '';

                    $has_search = !empty($url_location) || $url_guests > 0 || !empty($url_checkin);

                    // if URL params exist - skip transient and run fresh query
                    if($has_search){
                        $conditions   = [];
                        $conditions[] = "status = 'active'"; //CONDIUTION FOR ACTIVE ONLY

                        if($url_guests > 0)       $conditions[] = $wpdb->prepare("pax >= %d", $url_guests);
                        if($url_location != '')   $conditions[] = $wpdb->prepare("home_port = %s", $url_location);

                        if(!empty($url_checkin) && !empty($url_checkout)){
                            
                            $availability_table = $wpdb->prefix . 'yacht_availability';
                            $booked_codes = $wpdb->get_col($wpdb->prepare(
                                "SELECT DISTINCT yacht_code FROM $availability_table
                                WHERE start_date <= %s AND end_date >= %s",
                                $url_checkout, $url_checkin
                            ));
                            if(!empty($booked_codes)){
                                $placeholders = implode(',', array_fill(0, count($booked_codes), '%s'));
                                $conditions[] = $wpdb->prepare("yacht_code NOT IN ($placeholders)", ...$booked_codes);
                            }
                        }

                        $where  = "WHERE " . implode(" AND ", $conditions);
                        $total  = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
                        $yachts = $wpdb->get_results(
                            "SELECT name, home_port, length_feet, type, pax, low_price, yacht_code, main_image
                            FROM $table $where LIMIT 9"
                        );

                    } else {
                        // no URL params - use transient cache
                       $yachts = get_transient('yacht_initial_results');
                        $total  = get_transient('yacht_initial_total');

                        if(!$yachts || !$total){
                            $total  = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
                            $yachts = $wpdb->get_results(
                                "SELECT name, home_port, length_feet, type, pax, low_price, yacht_code, main_image
                                FROM $table WHERE status = 'active' LIMIT 9"
                            );
                            set_transient('yacht_initial_results', $yachts, 6 * HOUR_IN_SECONDS);
                            set_transient('yacht_initial_total', $total, 6 * HOUR_IN_SECONDS);
                        }
                    }

                    $first = true;
                    foreach($yachts as $yacht){
                        $yacht->is_first = $first;
                        $first = false;
                        include plugin_path . 'yacht-archive/templates/yacht_card.php';
                    }
                    ?>
       </div>
            </div>

            <?php if(9 < $total): ?>
            <div class="yacht-load-more-wrap">
                <button class="yacht-load-more" data-page="2">Load More</button>
            </div>
            <?php endif; ?>

        </div>

    </div> <!-- END MAIN SECTION -->

</div> <!-- END FLEX ROW -->

<?php } ?>