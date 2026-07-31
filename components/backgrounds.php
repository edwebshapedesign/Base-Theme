<?php if (get_field('background_type') === 'background_full'): ?>
<div class="background-overlay"></div>
<img src="<?php echo get_field('background_image'); ?>" loading="lazy" sizes="(max-width: 2048px) 100vw, 2048px" srcset="<?php echo get_field('background_image'); ?>" alt="<?php echo get_field('title'); ?>" class="background-image">
<?php endif; ?>

<?php if (get_field('background_type') === 'background_grad'): ?>
<div class="background-overlay blue"></div>
<img src="<?php echo get_field('background_image'); ?>" loading="lazy" sizes="(max-width: 2048px) 100vw, 2048px" srcset="<?php echo get_field('background_image'); ?>" alt="<?php echo get_field('title'); ?>" class="background-image">
<?php endif; ?>

<?php if (get_field('background_type') === 'dots_bottom'): ?>
<div class="background-dots"></div>
<?php endif; ?>

<?php if (get_field('background_type') === 'dots_top'): ?>
<div class="background-dots top"></div>
<?php endif; ?>