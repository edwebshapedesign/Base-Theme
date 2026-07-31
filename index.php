<?php get_header(); ?>
<div class="page-heading wf-section">
    <div class="page-heading-overlay">
      <h1>OUR BLOGS</h1>
    </div>
    <?php $image = get_field('archive_image', 'option'); ?>
    <img src="<?php echo $image['url']; ?>" loading="lazy" sizes="100vw" srcset="<?php echo wp_get_attachment_image_srcset($image['ID']); ?>" alt="" class="page-heading-background-image">
  </div>

  <?php if( have_posts() ): ?>
  <div class="body-section centered wf-section">
    <div class="body-container w-container">
      <div class="w-layout-grid blog-post-grid">
      <?php while( have_posts() ): the_post(); ?>
        <div id="w-node-f5c270a6-b5db-14c6-94ba-9be838c4a11e-b9048e7a" class="blog-post-holder">
          <a href="<?php echo get_permalink(); ?>" class="blog-post-holder-image-link w-inline-block"><img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id()); ?>" loading="lazy" sizes="(max-width: 479px) 87vw, (max-width: 767px) 92vw, (max-width: 991px) 90vw, (max-width: 1919px) 80vw, 1400px" srcset="<?php echo wp_get_attachment_image_srcset(get_post_thumbnail_id()); ?>" alt="" class="blog-post-holder-image"></a>
          <a href="<?php echo get_permalink(); ?>" class="w-inline-block">
            <h3 class="blog-post-holder-title"><?php the_title(); ?></h3>
          </a>
          <p><?php echo substr(get_the_excerpt(), 0 , 16); ?>...</p>
          <a href="<?php echo get_permalink(); ?>" class="button-two blogs w-button">Read More</a>
        </div>
      <?php endwhile; ?>

      </div>
    </div>
  </div>

<?php endif; ?>

<?php get_template_part('components/blocks/contact_form'); ?>
  
      
<?php get_footer(); ?>