<?php 

/*
 added options
send post of selected one using ajax js
added selective with the condition base on the col names
and displayed
 */
function yacht_sorting(){

global $wpdb;

$table = $wpdb->prefix . 'temp_yacht_details';
$sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : '';

$order = "name ASC";

if($sort == "price_low") $order = "low_price ASC";
if($sort == "price_high") $order = "low_price DESC";
if($sort == "name_az") $order = "name ASC";
if($sort == "name_za") $order = "name DESC";
if($sort == "guest_low") $order = "pax ASC";
if($sort == "guest_high") $order = "pax DESC";
if($sort == "size_low") $order = "length_feet ASC";
if($sort == "size_high") $order = "length_feet DESC";


//======= GUESTS & alcoation FILETEING=================
$guests = isset($_POST['guests']) ? intval($_POST['guests']) : '';
$location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';

// build WHERE conditions
$conditions[] = "status = 'active'";

if($guests > 0) $conditions[] = $wpdb->prepare("pax = %d", $guests); //add guests if it is not empty
if($location != '') $conditions[] = $wpdb->prepare("home_port = %s", $location);




// get checkin and checkout from AJAX
$checkin  = isset($_POST['checkin'])  ? sanitize_text_field($_POST['checkin'])  : '';
$checkout = isset($_POST['checkout']) ? sanitize_text_field($_POST['checkout']) : '';

// if dates selected → find booked yachts and exclude them
if (!empty($checkin) && !empty($checkout)) {

    $availability_table = $wpdb->prefix . 'yacht_availability';

    // get all yacht codes that are BOOKED in selected date range
    $booked_codes = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT yacht_code 
         FROM $availability_table 
         WHERE start_date <= %s AND end_date >= %s",
        $checkout, // booking starts before our checkout
        $checkin   // booking ends after our checkin
    ));

    // exclude booked yachts from results
    if (!empty($booked_codes)) {
        $placeholders = implode(',', array_fill(0, count($booked_codes), '%s'));
        $conditions[] = $wpdb->prepare(
            "yacht_code NOT IN ($placeholders)",
            ...$booked_codes
        );
    }
}


// combine conditions
$where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

$yachts = $wpdb->get_results(" SELECT name, home_port, length_feet, type, pax, low_price, yacht_code, main_image
                               FROM $table WHERE status = 'active' LIMIT 9 ");
?>

<div class="row g-4">

<?php if(empty($yachts)): ?>
    <p class="text-white text-center w-100">No yachts found matching your criteria.</p>
<?php else: ?>
    <?php foreach($yachts as $yacht){
        include plugin_path . 'yacht-archive/templates/yacht_card.php';
    } ?>
<?php endif; ?>

</div>

<?php
wp_die();
}