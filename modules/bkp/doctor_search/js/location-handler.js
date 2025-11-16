(function ($, Drupal) {
  Drupal.behaviors.locationDetect = {
    attach: function (context, settings) {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (pos) {
          $('[name="lat"]').val(pos.coords.latitude);
          $('[name="lng"]').val(pos.coords.longitude);
        });
      }
    }
  };
})(jQuery, Drupal);
