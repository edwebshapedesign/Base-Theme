<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer">
  <div class="container top">
    <div class="footer__top">
      <a href="<?php echo home_url(); ?>" class="footer__logo" aria-label="Lumisol home">
        <img src="<?php echo get_field('footer_logo','option'); ?>" alt="<?php echo get_field('footer_logo_alt','option'); ?>">
      </a>
      <button class="footer__totop" id="footerTop" aria-label="Scroll to top">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
      </button>
    </div>
  </div>
  <div class="container">
    <div class="footer__main">
      <div class="footer__col footer__col--about">
        <?php if (get_field('business_description','option')): ?><p class="footer__about"><?php echo get_field('business_description','option'); ?></p><?php endif; ?>
      <?php if (get_field('bus_for_good','option')): ?><img src="<?php echo get_field('bus_for_good','option'); ?>" class="footer_bus_icon"><?php endif; ?>
      <?php if (get_field('bus_for_good','option')): ?>
      
      <?php $fts = get_field('awards','options'); ?>
        <?php if($fts): ?>
        <div class="ft-grid">
          <?php foreach($fts as $ft): ?>
<img src="<?php echo $ft['award']; ?>" alt="<?php echo $ft['alt_text']; ?>" class="ft-item">
<?php endforeach; ?>
</div>
<?php endif; ?>
      
      <?php endif; ?>
      </div>
      <div class="footer__links">

        <?php $fitems = get_field('footer_columns','option'); ?>
        <?php if($fitems): ?>
          <?php foreach($fitems as $fitem): ?>
            <div class="footer__col">
              <h4><?php echo $fitem['title']; ?></h4>
              <?php $ftitems = $fitem['links']; ?>
              <?php if($ftitems): ?>
                <ul>
                  <?php foreach($ftitems as $ftitem): ?>
                    <li><a href="<?php echo $ftitem['link']; ?>"><?php echo $ftitem['title']; ?></a></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php $socials = get_field('social_icon','option'); ?>
        <?php if($socials): ?>
          <div class="footer__col footer__col--socials">
            <h4>Socials</h4>
            <ul>
              <?php foreach($socials as $social): ?>
                <?php
                  $socialtitle = $social['icon_name'];
                  $ssanitised = preg_replace("/[^a-zA-Z]/", "", $socialtitle);
                ?>
                <style>
                  .social-media-icon.<?php echo $ssanitised; ?> {
                    -webkit-mask-image: url(<?php echo $social['icon']; ?>);
                    mask-image: url(<?php echo $social['icon']; ?>);
                    -webkit-mask-repeat: no-repeat;
                    mask-repeat: no-repeat;
                    mask-size: contain;
                    mask-position: center;
                  }
                </style>
                <li><a target="_blank" rel="noopener" href="<?php echo $social['link']; ?>"><?php echo $social['icon_name']; ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      </div>
      <img class="footer__bolt" src="<?php echo get_template_directory_uri(); ?>/images/Lumisol-Icon-Gradient.svg" alt="Lumisol Bolt" aria-hidden="true">
    </div>
  </div>
  <div class="footer__bottom">
    <div class="container">
      &copy; <?php echo date('Y'); ?> Lumisol Ltd. All Rights Reserved. &middot; Company No. 15074675 &middot; VAT No. 447 3631 84  | web design by <a href="https://buildyourtrade.com/" target="_blank" rel="noopener">Build Your Trade</a>
    </div>
  </div>
</footer>

<style>
.lock-scroll {
    overflow: hidden;
    height: 100vh;
    touch-action: none;
}
</style>

<script>
  function lockScroll() {
    document.body.classList.toggle('lock-scroll');
  }
</script>

<script>
  document.addEventListener('wpcf7mailsent', function(event) {
    window.location.replace("<?php echo get_permalink(904); ?>");
  }, false);
</script>

<?php $footerscripts = get_field('footer_scripts','option'); ?>
<?php if($footerscripts): ?>
  <?php foreach($footerscripts as $footerscript): ?>
    <?php echo $footerscript['script']; ?>
  <?php endforeach; ?>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>