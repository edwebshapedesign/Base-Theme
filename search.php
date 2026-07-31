<?php
/**
 * Search Results Template — Lumisol
 *
 * Displays WordPress search results across all public post types
 * (posts, pages, services, case studies, areas — whatever the site
 * exposes). Uses the same archive-grid + archive-card markup as
 * blog-archive.html so search results visually match every other
 * listing on the site. Includes a re-run search input at the top.
 */
get_header();

$query   = get_search_query();
$total   = (int) $GLOBALS['wp_query']->found_posts;
$plural  = ( 1 === $total ) ? 'result' : 'results';
?>

<section class="page-hero">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
      <span class="breadcrumbs__sep">/</span>
      <span class="breadcrumbs__current">Search Results</span>
    </nav>
    <span class="eyebrow">Search</span>
    <?php if ( '' !== $query ) : ?>
      <h1>Results for &ldquo;<?php echo esc_html( $query ); ?>&rdquo;</h1>
      <p class="page-hero__intro"><?php echo esc_html( $total ); ?> <?php echo esc_html( $plural ); ?> found.</p>
    <?php else : ?>
      <h1>Search Lumisol</h1>
      <p class="page-hero__intro">Solar, battery, EV charging, electrical or smart home — type below and we'll pull anything relevant.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- Re-run search form — reuses .filter-bar__search styling -->
    <form class="filter-bar" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <div class="filter-bar__search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <label for="search-input" class="screen-reader-text">Search for</label>
        <input
          type="search"
          id="search-input"
          name="s"
          value="<?php echo esc_attr( $query ); ?>"
          placeholder="Search the site…"
          aria-label="Search the site">
      </div>
      <button type="submit" class="btn btn--gradient btn--small">Search</button>
    </form>

    <?php if ( have_posts() ) : ?>

      <div class="archive-grid">
        <?php while ( have_posts() ) : the_post();
          $post_type_obj  = get_post_type_object( get_post_type() );
          $type_label     = $post_type_obj && isset( $post_type_obj->labels->singular_name )
                            ? $post_type_obj->labels->singular_name
                            : 'Page';
          $cats           = ( 'post' === get_post_type() ) ? get_the_category() : array();
          $cat_name       = ! empty( $cats ) ? $cats[0]->name : '';
          $meta_parts     = array_filter( array( $type_label, $cat_name ) );
        ?>
          <a href="<?php the_permalink(); ?>" class="archive-card">
            <?php if ( has_post_thumbnail() ) : ?>
              <div class="archive-card__media">
                <?php the_post_thumbnail( 'medium_large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
              </div>
            <?php endif; ?>
            <div class="archive-card__body">
              <span class="archive-card__meta"><?php echo esc_html( implode( ' · ', $meta_parts ) ); ?></span>
              <h3 class="archive-card__title"><?php the_title(); ?></h3>
              <p class="archive-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
              <div class="archive-card__foot">
                <?php if ( 'post' === get_post_type() ) : ?>
                  <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                <?php else : ?>
                  <span>&nbsp;</span>
                <?php endif; ?>
                <span class="archive-card__more">Read More</span>
              </div>
            </div>
          </a>
        <?php endwhile; ?>
      </div>

    <?php elseif ( '' !== $query ) : ?>

      <div class="archive-empty is-shown">
        <p><strong>No results for &ldquo;<?php echo esc_html( $query ); ?>&rdquo;.</strong></p>
        <p>Try a different keyword — or browse our <a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>">blog</a>, <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>">services</a> or <a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>">case studies</a>.</p>
      </div>

    <?php endif; ?>

  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-banner">
      <h2>Couldn't find what you were after?</h2>
      <p>Tell us what you're looking for and we'll point you in the right direction — usually within the same working day.</p>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--outline-light">Ask the Team</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
