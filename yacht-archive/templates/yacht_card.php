<?php 

?>
<div class="col-md-4">
<div class="card bg-dark text-white border-0 h-100">

<img src="<?php echo esc_url($yacht->main_image); ?>" class="card-img-top" loading="lazy">

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
</div>