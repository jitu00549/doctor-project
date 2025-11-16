(function ($, Drupal) {
  Drupal.behaviors.scrollConfirm = {
    attach: function (context) {
      $('form', context).once('scrollConfirm').on('submit', function () {
        setTimeout(function () {
          $('html, body').animate({ scrollTop: $(document).height() }, 600);
        }, 800);
      });
    }
  };
})(jQuery, Drupal);
