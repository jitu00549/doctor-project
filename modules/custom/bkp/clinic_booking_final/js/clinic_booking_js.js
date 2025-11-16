(function ($, Drupal) {
  Drupal.behaviors.showClinicInfo = {
    attach: function (context, settings) {
      $('.show-contact-btn', context).once('showContact').click(function () {
        var clinic = $(this).data('clinic');
        var phone = $(this).data('phone');
        alert('Clinic: ' + clinic + '\nPhone: ' + phone);
      });
    }
  };
})(jQuery, Drupal);


