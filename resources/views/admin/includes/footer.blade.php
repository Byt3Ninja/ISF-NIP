<p class="d-flex justify-content-between align-items-center">
    <span style="direction: ltr">
        Incubator Pro
        {{ config('app.version') ? 'v' . config('app.version') : '' }}
        {{ config('app.build') ? '(Build ' . config('app.build') . ')' : '' }}
        - &copy; {{ now()->year }}
    </span>

    <button class="btn btn-primary btn-icon scroll-top" type="button">
        <i class="ti ti-arrow-up"></i>
    </button>
</p>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    function scrollToTop(duration) {
      const scrollTopButton = document.querySelector('.scroll-top');

      if (scrollTopButton) {
        // Check to see if the window is top, if not, then display the button
        window.addEventListener('scroll', function() {
          if (window.scrollY > 200) {
            scrollTopButton.style.display = 'block';
          } else {
            scrollTopButton.style.display = 'none';
          }
        });

        // Click event to scroll to top
        scrollTopButton.addEventListener('click', function() {
          const startPosition = window.pageYOffset;
          const startTime = performance.now();

          function scrollAnimation(currentTime) {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);

            window.scrollTo(0, startPosition * (1 - easeInOutCubic(progress)));

            if (progress < 1) {
              requestAnimationFrame(scrollAnimation);
            }
          }

          function easeInOutCubic(t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
          }

          requestAnimationFrame(scrollAnimation);
        });
      }
    }

    scrollToTop(1000); // Scroll to top over 1 second
  });
</script>
