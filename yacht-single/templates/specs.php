<?php if (!defined('ABSPATH')) exit; ?>

<div class="yacht-section" id="specifications">
    <h2 class="section-title">Specifications</h2>

    <div class="yacht-two-col">

        <!-- LEFT — Main Specs -->
        <div class="yacht-col-left">
            <div class="yacht-specs-grid">

                <?php if(!empty($yacht->length_feet)): ?>
                <div class="spec-row">
                    <span class="spec-label">Length</span>
                    <span class="spec-value"><?php echo esc_html($yacht->length_feet); ?>ft / <?php echo esc_html($yacht->length_meter); ?>m</span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->beam)): ?>
                <div class="spec-row">
                    <span class="spec-label">Beam</span>
                    <span class="spec-value"><?php echo esc_html($yacht->beam); ?>m</span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->draft)): ?>
                <div class="spec-row">
                    <span class="spec-label">Draft</span>
                    <span class="spec-value"><?php echo esc_html($yacht->draft); ?>m</span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->type)): ?>
                <div class="spec-row">
                    <span class="spec-label">Type</span>
                    <span class="spec-value"><?php echo esc_html($yacht->type); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->pax)): ?>
                <div class="spec-row">
                    <span class="spec-label">Guests</span>
                    <span class="spec-value"><?php echo esc_html($yacht->pax); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->cabins)): ?>
                <div class="spec-row">
                    <span class="spec-label">Cabins</span>
                    <span class="spec-value"><?php echo esc_html($yacht->cabins); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->builder)): ?>
                <div class="spec-row">
                    <span class="spec-label">Builder</span>
                    <span class="spec-value"><?php echo esc_html($yacht->builder); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->year_built)): ?>
                <div class="spec-row">
                    <span class="spec-label">Year Built</span>
                    <span class="spec-value"><?php echo esc_html($yacht->year_built); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->refit_year)): ?>
                <div class="spec-row">
                    <span class="spec-label">Refit Year</span>
                    <span class="spec-value"><?php echo esc_html($yacht->refit_year); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->cruise_speed)): ?>
                <div class="spec-row">
                    <span class="spec-label">Cruise Speed</span>
                    <span class="spec-value"><?php echo esc_html($yacht->cruise_speed); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->max_speed)): ?>
                <div class="spec-row">
                    <span class="spec-label">Max Speed</span>
                    <span class="spec-value"><?php echo esc_html($yacht->max_speed); ?></span>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- RIGHT — Pricing + Location -->
        <div class="yacht-col-right">
            <div class="yacht-specs-grid">

                <?php if(!empty($yacht->low_price)): ?>
                <div class="spec-row">
                    <span class="spec-label">Price From</span>
                    <span class="spec-value"><?php echo esc_html($yacht->currency); ?> <?php echo number_format($yacht->low_price); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Price To</span>
                    <span class="spec-value"><?php echo esc_html($yacht->currency); ?> <?php echo number_format($yacht->high_price); ?></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Terms</span>
                    <span class="spec-value"><?php echo esc_html($yacht->terms); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->home_port)): ?>
                <div class="spec-row">
                    <span class="spec-label">Home Port</span>
                    <span class="spec-value"><?php echo esc_html($yacht->home_port); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->summer_area)): ?>
                <div class="spec-row">
                    <span class="spec-label">Summer Area</span>
                    <span class="spec-value"><?php echo esc_html($yacht->summer_area); ?></span>
                </div>
                <?php endif; ?>

                <?php if(!empty($yacht->winter_area)): ?>
                <div class="spec-row">
                    <span class="spec-label">Winter Area</span>
                    <span class="spec-value"><?php echo esc_html($yacht->winter_area); ?></span>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>