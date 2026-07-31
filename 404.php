<?php
/**
 * 404 (Not Found) Template — Lumisol
 *
 * Friendly error page that helps the visitor recover: clear message,
 * site search, and four quick-link cards into the main areas of the
 * site. Reuses the existing page-hero, filter-bar__search and
 * archive-card patterns so it sits visually alongside every other
 * inner page.
 */
get_header();
?>

<section class="page-hero error-404">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
      <span class="breadcrumbs__sep">/</span>
      <span class="breadcrumbs__current">Page Not Found</span>
    </nav>

    <span class="error-404__number" aria-hidden="true">404</span>
    <h1>This page has gone off-grid</h1>
    <p class="page-hero__intro">The page you're after has either moved, been renamed, or never existed. Don't worry — try a search below or jump straight into one of the most-visited corners of the site.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- Site search — reuses the .filter-bar__search styling -->
    <form class="filter-bar" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <div class="filter-bar__search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <label for="search-404" class="screen-reader-text">Search the site</label>
        <input type="search" id="search-404" name="s" placeholder="Search for solar, EV, electrical…" aria-label="Search the site">
      </div>
      <button type="submit" class="btn btn--gradient btn--small">Search</button>
    </form>

    <div class="section-head">
      <span class="eyebrow">Where would you like to go?</span>
      <h2>Popular pages</h2>
    </div>

    <div class="archive-grid">
      <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="archive-card">
        <div class="archive-card__media"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/DSC-6215@2x.png' ); ?>" alt=""></div>
        <div class="archive-card__body">
          <span class="archive-card__meta">Services</span>
          <h3 class="archive-card__title">Our Services</h3>
          <p class="archive-card__excerpt">Solar PV, battery storage, Tesla Powerwall, EV chargers and full electrical work — all in-house.</p>
          <div class="archive-card__foot"><span>&nbsp;</span><span class="archive-card__more">Browse</span></div>
        </div>
      </a>
      <a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>" class="archive-card">
        <div class="archive-card__media"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/DSC-6230@2x.png' ); ?>" alt=""></div>
        <div class="archive-card__body">
          <span class="archive-card__meta">Blog</span>
          <h3 class="archive-card__title">Guides &amp; Insights</h3>
          <p class="archive-card__excerpt">Plain-English advice on payback, batteries, EV chargers and choosing a Norfolk installer.</p>
          <div class="archive-card__foot"><span>&nbsp;</span><span class="archive-card__more">Read</span></div>
        </div>
      </a>
      <a href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>" class="archive-card">
        <div class="archive-card__media"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/DSC-6534@2x.png' ); ?>" alt=""></div>
        <div class="archive-card__body">
          <span class="archive-card__meta">Case Studies</span>
          <h3 class="archive-card__title">Recent Projects</h3>
          <p class="archive-card__excerpt">Real Norfolk installations — solar, battery, Tesla Powerwall and commercial systems with the numbers.</p>
          <div class="archive-card__foot"><span>&nbsp;</span><span class="archive-card__more">View</span></div>
        </div>
      </a>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="archive-card">
        <div class="archive-card__media"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/DSC-6235@2x.png' ); ?>" alt=""></div>
        <div class="archive-card__body">
          <span class="archive-card__meta">Contact</span>
          <h3 class="archive-card__title">Talk to the Team</h3>
          <p class="archive-card__excerpt">Get a free Norfolk survey and an honest quote. Call 01603 327 719 or drop us a line.</p>
          <div class="archive-card__foot"><span>&nbsp;</span><span class="archive-card__more">Get in Touch</span></div>
        </div>
      </a>
    </div>

  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-banner">
      <h2>Still can't find it?</h2>
      <p>If you arrived from a link that should still work, let us know — we'll get it pointed at the right page.</p>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--outline-light">Report a Broken Link</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
