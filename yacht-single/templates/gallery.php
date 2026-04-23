<?php if (!defined('ABSPATH')) exit; ?>

<?php if(!empty($yacht->gallery)): ?>

<div class="yacht-section"id="gallery">
    <h2 class="section-title">Gallery</h2>

    <div class="yacht-gallery-grid">
        <?php foreach($yacht->gallery as $image): ?>
            <?php if(!empty($image)): ?>
            <div class="gallery-item">
                <a href="<?php echo esc_url($image); ?>" >
                    <img src="<?php echo esc_url($image); ?>"
                         alt="<?php echo esc_attr($yacht->name); ?>"
                         loading="lazy">
                </a>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

</div>

<?php endif; ?>