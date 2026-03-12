<?php 
add_shortcode( 'all_yachtes', 'all_yachtes' );

function all_yachtes(){ ?>
<div class="container p-5 text-center">
<h2>Explore Our Extensive Fleet of Crewed Charter Yachts</h2>
<p>Discover our curated selection of crewed charter yachts across the Virgin Islands, Caribbean, Bahamas, and Mediterranean. Our experienced team of Brokers is ready to guide you through the options and connect you with the perfect yacht and crew for your dream vacation.</p>
</div>



<!-- SIDEBAR     -->
<div class="container" id="sidebar"> 
<?php get_template_part('yacht_archive_sidebar.php'); ?>
</div>



<!--- ------  MAIN SECTION ------------------  -->

<div class="container">
<!-- SORTING ORDER -->
 <select id="yacht_sort">
<option value="">Default</option>
<option value="price_low">Price (low to high)</option><div class="row g-4"></div>
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
<?php echo '<div class="row g-4">'; ?>

<?php
global $wpdb;
$table = $wpdb->prefix . 'temp_yacht_details';

$yachts = $wpdb->get_results("SELECT * FROM $table LIMIT 9");

foreach($yachts as $yacht){
?>

<div class="col-md-4">
<div class="card bg-dark text-white border-0 h-100">

<img src="<?php echo esc_url($yacht->main_image_api); ?>" class="card-img-top">

<div class="card-body">

<h5 class="fw-bold text-uppercase">
<?php echo esc_html($yacht->name); ?>
</h5>

<p><strong>Location:</strong> <?php echo esc_html($yacht->home_port); ?></p>

<p>
<?php echo esc_html($yacht->length_feet); ?>FT
<?php echo esc_html($yacht->type); ?> -
<?php echo esc_html($yacht->pax); ?> Guests
</p>

<p>Pricing From: €<?php echo esc_html($yacht->low_price); ?>/Week</p>

<a href="/single-yacht-page/?usr_id_in=<?php echo esc_attr($yacht->yacht_code); ?>" class="btn btn-primary">
VIEW YACHT
</a>

</div>
</div>
<?php echo '</div>';?>

<?php } ?>

</div>
</div>
</div>





<?php } //end of shortcode
 ?>