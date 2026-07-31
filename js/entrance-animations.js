/* ==========================================================================
   Entrance animations — fade in up (vanilla JS, IntersectionObserver)
   Each element animates when it individually enters the viewport.
   Siblings entering together are staggered top-to-bottom; a later batch
   (e.g. benefits row 2, or each faq__item as you scroll) re-staggers fresh.
   ========================================================================== */
(function () {
  // Mark the document so the CSS hidden-state applies. If you load this file
  // deferred at the end of <body>, ALSO add this one-liner in <head> to stop
  // a flash of content before hiding kicks in:
  //   <script>document.documentElement.classList.add('has-anim');</script>
  document.documentElement.classList.add('has-anim');
 
  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
 
  var SELECTOR = [
    '.section-head',
    '.intro',
    '.btn',
    '.cta-banner',
    '.accreditations__row',
    '.services__grid',
    '.testimonials__viewport',
    '.video__frame',
    '.page-hero__intro',
    '.about__grid > *',     /* about__content, about__media */
    '.posts__grid > *',
    '.stats__grid > *',
    '.benefits__grid > *',
    '.contact__grid > *',
    '.team-grid > *',
    '.archive-grid > *',
    '.accreditations__logos > *',
    '.gallery > *',
    '.faq__item'
  ].join(',');

  /* --- Timing knobs ------------------------------------------------------- */
  var BASE_DELAY = 450; // ms — wait this long AFTER an element becomes visible
  var STAGGER    = 150;  // ms — gap between siblings revealing in the same wave
  var WAVE_GAP   = 600;  // ms — silence longer than this starts a fresh wave
  /* ------------------------------------------------------------------------ */

  function init() {
    var elements = document.querySelectorAll(SELECTOR);
    if (!elements.length) return;
 
    // Reduced motion / no IO support: reveal everything immediately
    if (prefersReduced || !('IntersectionObserver' in window)) {
      elements.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
 
    // Per-parent "wave" tracking so staggering resets between rows/batches
    var waves = new Map();
 
    var io = new IntersectionObserver(function (entries) {
      entries
        .filter(function (e) { return e.isIntersecting; })
        // reveal in document order for a natural top-to-bottom stagger
        .sort(function (a, b) {
          var pos = a.target.compareDocumentPosition(b.target);
          return (pos & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1;
        })
        .forEach(function (entry) {
          var el = entry.target;
          io.unobserve(el); // once only
 
          var parent = el.parentElement;
          var now = performance.now();
          var wave = waves.get(parent);
 
          // start a new wave if this parent hasn't revealed anything recently
          if (!wave || now - wave.lastTime > WAVE_GAP) {
            wave = { count: 0, lastTime: now };
          }
 
          var delay = BASE_DELAY + wave.count * STAGGER;
          wave.count += 1;
          wave.lastTime = now;
          waves.set(parent, wave);
 
          // Apply the entrance transition INLINE, only for this reveal.
          el.style.willChange = 'opacity, transform';
          el.style.transition = 'all var(--fade-duration) var(--fade-easing)';
          el.style.transitionDelay = delay + 'ms';
          el.classList.add('is-visible');
 
          // Once the fade finishes, strip the inline styles so the element
          // hands control back to its own CSS transition (e.g. the FAQ
          // accordion). A timeout backs up the transitionend in case it
          // doesn't fire (element hidden, interrupted, etc.).
          var cleaned = false;
          function cleanup() {
            if (cleaned) return;
            cleaned = true;
            el.style.transition = '';
            el.style.transitionDelay = '';
            el.style.willChange = '';
            el.removeEventListener('transitionend', onEnd);
          }
          function onEnd(e) {
            // ignore transitionend bubbling up from child elements
            if (e.target !== el) return;
            if (e.propertyName !== 'opacity' && e.propertyName !== 'transform') return;
            cleanup();
          }
          el.addEventListener('transitionend', onEnd);
          setTimeout(cleanup, delay + 600); // base delay + duration + buffer
        });
    }, { threshold: 0.15 });
 
    elements.forEach(function (el) { io.observe(el); });
  }
 
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
