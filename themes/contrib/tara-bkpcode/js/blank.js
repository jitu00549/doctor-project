(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.locationDetect = {
    attach: function (context) {
      once('locationDetect', '#btn-location-detect', context).forEach(function (btn) {
        btn.addEventListener('click', function () {
          let input = document.getElementById('edit-combine-1');

          if (!navigator.geolocation) {
            alert("Geolocation not supported in this browser.");
            return;
          }

          input.value = "Getting location...";

          navigator.geolocation.getCurrentPosition(
            function (pos) {
              let lat = pos.coords.latitude;
              let lon = pos.coords.longitude;
              input.value = lat + "," + lon;
            },
            function (err) {
              alert("Error: " + err.message);
            },
            {
              enableHighAccuracy: true,
              timeout: 10000,
              maximumAge: 0
            }
          );
        });
      });
    }
  };

})(Drupal, once);