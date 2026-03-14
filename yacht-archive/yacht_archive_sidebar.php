<?php

function yacht_sidebar(){
?>
<div class="all_sidebar">
<?php global $wpdb;
$table = $wpdb->prefix . 'temp_yacht_details';

$guests = $wpdb->get_col("SELECT DISTINCT pax FROM $table WHERE pax IS NOT NULL ORDER BY pax ASC");
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
</div>




<!-- LOCATION------------------ -->
 <?php


// Get unique locations
$locations = $wpdb->get_col(
    "SELECT DISTINCT home_port 
     FROM {$table} 
     WHERE home_port IS NOT NULL AND home_port != '' 
     ORDER BY home_port ASC"
);
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

<?php } ?>
