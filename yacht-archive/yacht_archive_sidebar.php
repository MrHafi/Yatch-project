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
