<?php $buttons = get_field('buttons'); ?>
        <?php if($buttons): ?>
    <?php foreach($buttons as $button): ?>
    
    <a href="<?php echo $button['button_link']; ?>" class="btn <?php if ($button['button_style']): ?><?php echo $button['button_style']; ?><?php else: ?>btn--gradient<?php endif; ?>"><?php echo $button['button_text']; ?></a>
    
    <?php endforeach; ?>
      <?php endif; ?>