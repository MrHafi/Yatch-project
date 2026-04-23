<?php if (!defined('ABSPATH')) exit; ?>

<?php if(!empty($yacht->captain_name) || !empty($yacht->crew_profile)): ?>

<div class="yacht-section">
    <h2 class="section-title">Crew</h2>

  <div class="yacht-section" id="crew">


        <!-- CAPTAIN NAME -->
        <?php if(!empty($yacht->captain_name)): ?>
        <div class="crew-captain">
            <span class="crew-label">Captain</span>
            <span class="crew-name"><?php echo esc_html($yacht->captain_name); ?></span>
        </div>
        <?php endif; ?>

        <!-- CREW PROFILE -->
        <?php if(!empty($yacht->crew_profile)): ?>
        <div class="crew-profile">
            <?php echo wp_kses_post($yacht->crew_profile); ?>
        </div>
        <?php endif; ?>

    </div>

</div>

<?php endif; ?>