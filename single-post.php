<?php
/**
 * Template Name: Single Post
 * Single blog post template for Lumisol.
 *
 * Body content is rendered via the_content() — blocks (blog_editor, blog_faqs etc.)
 * are added to the post and render themselves, including any schema output.
 *
 * Required ACF field groups:
 *   - Post Content (location: Post Type = Post)
 *       intro                (textarea — hero intro paragraph)
 *   - Author Profile (location: User Form = All)
 *       author_photo         (image — return: array)
 *       author_bio           (textarea)
 *       author_role          (text, optional — e.g. "Founder")
 */

get_header();

while ( have_posts() ) : the_post();

  $post_id      = get_the_ID();
  $author_id    = get_post_field( 'post_author', $post_id );
  $primary_cat  = get_the_category( $post_id );
  $primary_cat  = ! empty( $primary_cat ) ? $primary_cat[0] : null;

  // Render content once — used for both read time and body output
  $rendered_content = apply_filters( 'the_content', get_the_content() );
  $word_count       = str_word_count( wp_strip_all_tags( $rendered_content ) );
  $read_time        = max( 1, (int) ceil( $word_count / 200 ) );
  ?>


<section class="page-hero">
  <div class="container">
    <nav class="breadcrumbs">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span class="breadcrumbs__sep">/</span>
      <a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ); ?>">Blog</a><span class="breadcrumbs__sep">/</span>
      <?php if ( $primary_cat ) : ?>
        <span class="breadcrumbs__current"><?php echo esc_html( $primary_cat->name ); ?></span>
      <?php endif; ?>
    </nav>
    <span class="eyebrow">
      <?php if ( $primary_cat ) echo esc_html( $primary_cat->name ) . ' · '; ?><?php echo esc_html( $read_time ); ?> min read
    </span>
    <h1><?php the_title(); ?></h1>
    <?php $intro = get_field( 'intro' ); ?>
    <?php if ( $intro ) : ?>
      <p class="page-hero__intro"><?php echo esc_html( $intro ); ?></p>
    <?php elseif ( has_excerpt() ) : ?>
      <p class="page-hero__intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section blog-stuff">
  <div class="container">
    <div class="with-sidebar">
      <article class="article">

        <div class="article-meta">
          <span><span class="article-meta__tag">By</span> <?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></span>
          <span><span class="article-meta__tag">Published</span> <?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
          <?php if ( $primary_cat ) : ?>
            <span><span class="article-meta__tag">Category</span> <?php echo esc_html( $primary_cat->name ); ?></span>
          <?php endif; ?>
        </div>

        <?php if ( has_post_thumbnail() ) : ?>
          <div class="article__feature">
            <?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
          </div>
        <?php endif; ?>

        <?php
        // ============================================================
        // BODY — blocks render via the_content()
        // ============================================================
        ?>
        <div class="blog-body">
          <?php echo $rendered_content; ?>
        </div>

        <?php
        // ============================================================
        // AUTHOR CONTACT CARD
        // ============================================================
        $author_photo = get_field( 'author_photo', 'user_' . $author_id );
        $author_bio   = get_field( 'author_bio',   'user_' . $author_id );
        $author_role  = get_field( 'author_role',  'user_' . $author_id );
        $author_name  = get_the_author_meta( 'display_name', $author_id );
        $phone        = get_field( 'company_phone', 'option' ) ?: '01603 327 719';
        ?>
       <?php if ( $author_photo ) : ?>
        <div class="contact-card">
          
            <div class="contact-card__photo">
              <img src="<?php echo esc_url( $author_photo['sizes']['medium'] ?? $author_photo['url'] ); ?>"
                   alt="<?php echo esc_attr( $author_photo['alt'] ?: $author_name ); ?>">
            </div>
          
          </div><?php endif; ?>
          
              <div class="cta-banner top-marg">
      <h2><?php echo get_field('cta_title','option'); ?></h2>
      <?php echo get_field('cta_text','option');; ?>
      <a href="<?php echo get_field('cta_link','option'); ?>" class="btn btn--outline-light">Get In Touch</a>
    </div>
   

      </article>

      <aside class="with-sidebar__aside">

        <?php
        // ============================================================
        // SIDEBAR — CATEGORIES (dynamic)
        // ============================================================
        $categories = get_categories( [
          'hide_empty' => true,
          'orderby'    => 'count',
          'order'      => 'DESC',
        ] );
        if ( ! empty( $categories ) ) : ?>
          <div class="aside-section">
            <h4>Categories</h4>
            <ul class="cat-list">
              <?php foreach ( $categories as $cat ) : ?>
                <li>
                  <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="cat-list__count"><?php echo (int) $cat->count; ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php
        // ============================================================
        // SIDEBAR — RECENT POSTS (dynamic, excludes current post)
        // ============================================================
        $recent = new WP_Query( [
          'post_type'           => 'post',
          'posts_per_page'      => 6,
          'post__not_in'        => [ $post_id ],
          'ignore_sticky_posts' => true,
          'no_found_rows'       => true,
        ] );
        if ( $recent->have_posts() ) : ?>
          <div class="aside-section">
            <h4>Recent Posts</h4>
            <ul class="post-list">
              <?php while ( $recent->have_posts() ) : $recent->the_post(); ?>
                <li>
                  <a href="<?php the_permalink(); ?>">
                    <div class="post-list__thumb">
                      <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'thumbnail', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
                      <?php endif; ?>
                    </div>
                    <div>
                      <h5 class="post-list__title"><?php the_title(); ?></h5>
                      <span class="post-list__date"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
                    </div>
                  </a>
                </li>
              <?php endwhile; ?>
            </ul>
            <a href="<?php echo get_field('blog_page','option'); ?>" class="btn btn--gradient">View All Posts</a>
          </div>
        <?php endif; wp_reset_postdata(); ?>

      </aside>
    </div>
  </div>
</section>

<?php endwhile;

get_footer();