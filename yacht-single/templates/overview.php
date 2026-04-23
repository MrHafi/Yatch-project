<?php if (!defined('ABSPATH')) exit; ?>

<div class="yacht-section" id="overview">
    <h2 class="section-title">Overview</h2>

    <div class="yacht-two-col">

        <!-- LEFT — Description + Accommodations -->
        <div class="yacht-col-left">
            <?php if(!empty($yacht->description)): ?>
                <div class="yacht-description">
                    <?php echo wp_kses_post($yacht->description); ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($yacht->accommodations)): ?>
                <h3 class="sub-title">Accommodations</h3>
                <div class="yacht-accommodations">
                    <?php echo wp_kses_post($yacht->accommodations); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT — Layout Image -->
        <div class="yacht-col-right">
            <?php if(!empty($yacht->layout_image)): ?>
                <div class="yacht-layout">
                    <h3 class="sub-title">Layout</h3>
                    <img src="<?php echo esc_url($yacht->layout_image); ?>"
                         alt="<?php echo esc_attr($yacht->name); ?> Layout"
                         loading="lazy">
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>