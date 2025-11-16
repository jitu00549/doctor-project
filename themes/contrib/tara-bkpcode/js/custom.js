(function ($, Drupal) {
  Drupal.behaviors.detectLocation = {
    attach: function (context, settings) {

      $('#detectLocationBtn', context).once('detectLocation').click(function () {

        if (!navigator.geolocation) {
          alert('Your browser does not support Geolocation');
          return;
        }

        navigator.geolocation.getCurrentPosition(function (position) {

          let lat = position.coords.latitude;
          let lon = position.coords.longitude;

          let url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;

          fetch(url)
            .then(response => response.json())
            .then(data => {
              if (data && data.display_name) {
                $('#edit-combine-1').val(data.display_name);
                $('#locationOutput').html("📍 " + data.display_name);
              } else {
                alert("Unable to get your address!");
              }
            })
        }, function (error) {
          alert("Location Permission Denied!");
        });
      });

      $('#manualSearchBtn', context).once('manualSearch').click(function () {
        let location = $('#edit-combine-1').val();
        if (location.trim() === "") {
          alert("Please enter a location!");
        } else {
          $('#locationOutput').html("🔍 You searched: " + location);
        }
      });
    }
  };
})(jQuery, Drupal);