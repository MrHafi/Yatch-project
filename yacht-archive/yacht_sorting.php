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
    // PAGINATION
$page   = isset($_POST['page']) ? intval($_POST['page']) : 1; //current page
$limit  = 9; 
$offset = ($page - 1) * $limit; //where to start the product like 9 then 18 then 27

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
// BOAT TYPE
$boat_type = isset($_POST['boat_type']) ? sanitize_text_field($_POST['boat_type']) : '';
if($boat_type != '') $conditions[] = $wpdb->prepare("type = %s", $boat_type);


// PRICING...........................................
$price_ranges = isset($_POST['price_ranges']) ? $_POST['price_ranges'] : [];

if(!empty($price_ranges)){
    $price_conditions = [];
    foreach($price_ranges as $range){
        $parts = explode('-', sanitize_text_field($range));  //min value - max value in an array
        if(count($parts) === 2){
            $min = floatval($parts[0]);
            $max = floatval($parts[1]);
            $price_conditions[] = $wpdb->prepare("(low_price >= %f AND low_price <= %f)", $min, $max);
        }
    }
    if(!empty($price_conditions)){
        $conditions[] = '(' . implode(' OR ', $price_conditions) . ')';
    }
}


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

$total_matching_yachts  = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

$yachts = $wpdb->get_results(
    "SELECT name, home_port, length_feet, type, pax, low_price, yacht_code, main_image
     FROM $table $where ORDER BY $order LIMIT $limit OFFSET $offset"
);
?>

<!-- CARDS  -->
<div class="row g-4">

<?php if(empty($yachts)): ?> 
    <p class="text-white text-center w-100">No yachts found matching your criteria.</p>
<?php else: ?>
    <?php foreach($yachts as $yacht){
        include plugin_path . 'yacht-archive/templates/yacht_card.php';
    } ?>
<?php endif; ?>

</div>


<!-- PAGINATION  if more yachts are there-->
 <?php if(($offset + $limit) < $total_matching_yachts): ?>
<div class="yacht-load-more-wrap">
    <button class="yacht-load-more" data-page="<?php echo $page + 1; ?>">
        Load More
    </button>
</div>
<?php endif; ?>

<?php
wp_die();
}