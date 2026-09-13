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

<?php // Footer scripts from Site Content → Site Scripts are output on wp_footer by functions.php. ?>

<?php wp_footer(); ?>

</body>
</html>