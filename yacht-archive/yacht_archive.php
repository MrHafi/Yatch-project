<?php
add_shortcode('all_yachtes','all_yachtes');

function all_yachtes(){ ?>

<div class="container p-5 text-center">
<h2>Explore Our Extensive Fleet of Crewed Charter Yachts</h2>
<p>Discover our curated selection of crewed charter yachts...</p>
</div>

<div class="all_page_template">
    <!-- SIDEBAR -->
<div class="" id="sidebar">
<?php yacht_sidebar(); ?>

</div>

<div class="main_section">

<div class="container d-flex align-item-end"> <!-- SORTING -->
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
$yachts = $wpdb->get_results("SELECT * FROM $table LIMIT 9");

foreach($yachts as $yacht){

include plugin_path . 'yacht-archive/templates/yacht_card.php';


}
?>

</div>
</div>
</div>
</div></div>
<?php }