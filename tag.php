<?php
/**
 * Tag Archive Template — Lumisol
 *
 * Displays all posts assigned to a single tag. Same markup as
 * category.php — archive-grid + archive-card via The Loop — with
 * no filter bar and no pagination.
 */
get_header();
?>

<section class="page-hero">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
      <span class="breadcrumbs__sep">/</span>
      <a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>">Blog</a>
      <span class="breadcrumbs__sep">/</span>
      <span class="breadcrumbs__current">#<?php single_tag_title(); ?></span>
    </nav>
    <span class="eyebrow">Tag</span>
    <h1>#<?php single_tag_title(); ?></h1>
    <?php if ( tag_description() ) : ?>
      <div class="page-hero__intro"><?php echo wp_kses_post( tag_description() ); ?></div>
    <?php else : ?>
      <p class="page-hero__intro">All Lumisol posts tagged <strong><?php single_tag_title( '', true ); ?></strong>.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ( have_posts() ) : ?>

      <div class="archive-grid">
        <?php while ( have_posts() ) : the_post();
          $cats     = get_the_category();
          $cat_name = ! empty( $cats ) ? $cats[0]->name : '';
        ?>
          <a href="<?php the_permalink(); ?>" class="archive-card">
            <?php if ( has_post_thumbnail() ) : ?>
              <div class="archive-card__media">
                <?php the_post_thumbnail( 'medium_large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
              </div>
            <?php endif; ?>
            <div class="archive-card__body">
              <span class="archive-card__meta">
                <?php echo esc_html( $cat_name ); ?>
                <?php
                $reading_time = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
                echo ' · ' . esc_html( $reading_time ) . ' min read';
                ?>
              </span>
              <h3 class="archive-card__title"><?php the_title(); ?></h3>
              <p class="archive-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
              <div class="archive-card__foot">
                <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                <span class="archive-card__more">Read More</span>
              </div>
            </div>
          </a>
        <?php endwhile; ?>
      </div>

    <?php else : ?>

      <div class="archive-empty is-shown">
        <p><strong>Nothing tagged here yet.</strong></p>
        <p>Browse the <a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>">full blog</a> for our latest posts.</p>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php if (get_field('cta_title','option') && get_field('cta_text','option')): ?>
<section class="section">
  <div class="container">
    <div class="cta-banner">
      <h2><?php echo get_field('cta_title','option'); ?></h2>
      <p><?php echo get_field('cta_text','option'); ?></p>
      <a href="<?php echo get_field('cta_link','option'); ?>" class="btn btn--outline-light">Contact Us</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
