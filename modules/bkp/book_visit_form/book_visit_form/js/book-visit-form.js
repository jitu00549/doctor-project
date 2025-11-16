(function ($, Drupal) {
  Drupal.behaviors.bookVisitSlider = {
    attach: function (context, settings) {
      const images = once('book-visit-slider', '.slider-img', context);
      if (!images.length) return;
      let index = 0;
      setInterval(() => {
        images[index].classList.remove('active');
        index = (index + 1) % images.length;
        images[index].classList.add('active');
      }, 3000);

      const closeBtn = document.getElementById('close-form-btn');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () {
          const container = document.getElementById('book-visit-container');
          container.classList.add('fade-out');
          setTimeout(() => container.remove(), 500);
        });
      }
    }
  };
})(jQuery, Drupal);
