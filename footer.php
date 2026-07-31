<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer">
  
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