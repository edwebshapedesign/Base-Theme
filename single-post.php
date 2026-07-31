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

    if( have_posts() ){
        while(have_posts()){
          the_post();
          the_content();
        }
    }

get_footer();