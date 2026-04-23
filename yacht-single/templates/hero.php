<?php if (!defined('ABSPATH')) exit; ?>

<div class="yacht-single-hero">

    <!-- MAIN IMAGE -->
    <div class="yacht-hero-image">
        <img src="<?php echo esc_url($yacht->main_image); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
    </div>

    <!-- HERO INFO -->
    <div class="yacht-hero-info">

        <h1 class="yacht-name"><?php echo esc_html($yacht->name); ?></h1>

        <?php if(!empty($yacht->previous_name)): ?>
            <p class="yacht-prev-name">Previously: <?php echo esc_html($yacht->previous_name); ?></p>
        <?php endif; ?>

        <!-- PRICE -->
        <div class="yacht-price">
            <?php echo esc_html($yacht->currency); ?>
            <?php echo number_format($yacht->low_price); ?>
            -
            <?php echo number_format($yacht->high_price); ?>
            / <?php echo esc_html($yacht->terms); ?>
        </div>

        <!-- QUICK SPECS -->
        <div class="yacht-quick-specs">

            <div class="spec-item">
                <span class="spec-label">Type</span>
                <span class="spec-value"><?php echo esc_html($yacht->type); ?></span>
            </div>

            <div class="spec-item">
                <span class="spec-label">Length</span>
                <span class="spec-value"><?php echo esc_html($yacht->length_feet); ?>ft / <?php echo esc_html($yacht->length_meter); ?>m</span>
            </div>

            <div class="spec-item">
                <span class="spec-label">Guests</span>
                <span class="spec-value"><?php echo esc_html($yacht->pax); ?></span>
            </div>

            <div class="spec-item">
                <span class="spec-label">Cabins</span>
                <span class="spec-value"><?php echo esc_html($yacht->cabins); ?></span>
            </div>

            <div class="spec-item">
                <span class="spec-label">Location</span>
                <span class="spec-value"><?php echo esc_html($yacht->home_port); ?></span>
            </div>

            <div class="spec-item">
                <span class="spec-label">Built</span>
                <span class="spec-value"><?php echo esc_html($yacht->year_built); ?></span>
            </div>

        </div>

        <!-- BACK BUTTON -->
        <a href="<?php echo esc_url(home_url('/all-yachts/')); ?>" class="btn btn-primary">
            ← Back to All Yachts
        </a>

    </div>

</div>