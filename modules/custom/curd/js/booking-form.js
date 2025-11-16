(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.curdBookingForm = {
    attach: function (context, settings) {
      $('.booking-form-wrapper', context).once('curdBookingForm').each(function () {
        // Placeholder JS - no required logic.
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
