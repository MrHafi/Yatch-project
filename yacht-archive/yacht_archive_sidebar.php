<?php

function yacht_sidebar(){
?>
<div class="all_sidebar">
<?php global $wpdb;
$table = $wpdb->prefix . 'temp_yacht_details';

$guests = get_transient('yacht_sidebar_guests');

if(!$guests){
    $guests = $wpdb->get_col(
        "SELECT DISTINCT pax FROM $table WHERE pax IS NOT NULL AND status = 'active' ORDER BY pax ASC"
    );
    set_transient('yacht_sidebar_guests', $guests, 12 * HOUR_IN_SECONDS);
}
?>
<div class="guests_container"></div>
<select name="guests" class="p-2 p2 yacht_filter" id="guest_filter">
<option value="">Guests</option>

<?php
foreach($guests as $guest){
echo '<option value="'.$guest.'">'.$guest.' Guests</option>';
}
?>

</select>





<!-- LOCATION------------------ -->
 <?php


// Get unique locations
$locations = get_transient('yacht_sidebar_locations');

if(!$locations){
    $locations = $wpdb->get_col(
        "SELECT DISTINCT home_port FROM $table
         WHERE home_port IS NOT NULL AND home_port != ''
         AND status = 'active'
         ORDER BY home_port ASC"
    );
    set_transient('yacht_sidebar_locations', $locations, 12 * HOUR_IN_SECONDS);
}
?>

<div class="yacht-location-filters">
    <h5>Filter by Location</h5>

    <div class="form-check">
        <input class="form-check-input location-radio yacht_filter" type="radio" 
               name="yacht_location" id="loc_all" value="">
        <label class="form-check-label" for="loc_all">All Locations</label>
    </div>

    <?php foreach($locations as $loc): ?>
    <div class="form-check location-radio">
        <input class="form-check-input location-radio yacht_filter" type="radio" 
               name="yacht_location" 
               id="loc_<?php echo esc_attr($loc); ?>" 
               value="<?php echo esc_attr($loc); ?>">
        <label class="form-check-label" for="loc_<?php echo esc_attr($loc); ?>">
            <?php echo esc_html($loc); ?>
        </label>
    </div>
    <?php endforeach; ?>
</div>


</div>


<!-- BOAT TYPE -->
<div class="yacht-type-filter">
    <h5>Boat Type</h5>
    <select name="boat_type" class="yacht_filter form-control" id="boat_type_filter">
        <option value="">All Types</option>
        <option value="Cat">Catamaran</option>
        <option value="Power">Power</option>
        <option value="Sail">Sail</option>
        <option value="Motor">Motor</option>
    </select>
</div>

<!-- PRICE FILTER -->
<div class="yacht-price-filter">
    <h5>Price Range</h5>
    <div class="form-check">
        <input class="form-check-input price-filter" type="checkbox" id="price_1" value="0-25000">
        <label class="form-check-label" for="price_1">Under $25,000/week</label>
    </div>
    <div class="form-check">
        <input class="form-check-input price-filter" type="checkbox" id="price_2" value="25000-75000">
        <label class="form-check-label" for="price_2">$25,000 - $75,000/week</label>
    </div>
    <div class="form-check">
        <input class="form-check-input price-filter" type="checkbox" id="price_3" value="75000-99999999">
        <label class="form-check-label" for="price_3">Above $75,000/week</label>
    </div>
</div>

<!-------------- AVAILABILITY -->
<div class="yacht-availability-filter">
    <h5>Check Availability</h5>
    <label>Check-in</label>
    <input type="date" id="checkin" class="form-control">
    <label class="mt-2">Check-out</label>
    <input type="date" id="checkout" class="form-control mt-1">
    <button id="check_availability" class="btn btn-primary mt-2 w-100">Check Availability</button>
    <p id="availability_msg" style="margin-top:10px;"></p>
</div>




</div>
<?php } ?>
