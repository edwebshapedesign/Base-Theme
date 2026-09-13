/* ==========================================================================
   Primary navigation: mobile toggle.
   Markup: components/blocks/parts/nav.php   Styles: css/navigation.css
   ========================================================================== */
(function () {
  'use strict';

  var toggle = document.querySelector('.site-nav-toggle');
  var nav = document.getElementById('site-nav');
  if (!toggle || !nav) return;

  var DESKTOP = 992;

  function setOpen(open) {
    nav.classList.toggle('is-open', open);
    document.body.classList.toggle('nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', toggle.getAttribute(open ? 'data-label-close' : 'data-label-open'));
  }

  toggle.addEventListener('click', function () {
    setOpen(!nav.classList.contains('is-open'));
  });

  // Escape closes and returns focus to the toggle.
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) {
      setOpen(false);
      toggle.focus();
    }
  });

  // Click outside the header closes.
  document.addEventListener('click', function (e) {
    if (nav.classList.contains('is-open') && !e.target.closest('#site-header')) {
      setOpen(false);
    }
  });

  // Reset when resizing up to desktop so the panel state never leaks.
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth >= DESKTOP && nav.classList.contains('is-open')) setOpen(false);
    }, 100);
  });
})();
