(function ($) {
  $(document).ready(function () {
    var $input = $('#edit-combine-1');
    if ($input.length === 0) return;

    // Show "Detecting..." placeholder
    var originalPlaceholder = $input.attr('placeholder') || '';
    $input.attr('placeholder', 'Detecting location...');

    function setLocationValue(text) {
      $input.val(text);
      $input.attr('placeholder', originalPlaceholder);
    }

    // Reverse geocode coordinates → city, state, country
    function reverseGeocode(lat, lon) {
      var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
        + '&lat=' + encodeURIComponent(lat)
        + '&lon=' + encodeURIComponent(lon)
        + '&addressdetails=1';
      return $.getJSON(url).then(function (data) {
        var addr = data.address || {};
        var parts = [];
        if (addr.city) parts.push(addr.city);
        else if (addr.town) parts.push(addr.town);
        else if (addr.village) parts.push(addr.village);
        else if (addr.county) parts.push(addr.county);
        if (addr.state) parts.push(addr.state);
        if (addr.country) parts.push(addr.country);
        return parts.join(', ') || (lat + ',' + lon);
      });
    }

    // Fallback → IP-based location
    function ipFallback() {
      return $.getJSON('https://ipapi.co/json/').then(function (data) {
        var city = data.city || '';
        var region = data.region || '';
        var country = data.country_name || '';
        var parts = [];
        if (city) parts.push(city);
        if (region && region !== city) parts.push(region);
        if (country) parts.push(country);
        return parts.join(', ') || 'Unknown location';
      });
    }

    // Try browser geolocation first
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (pos) {
        var lat = pos.coords.latitude;
        var lon = pos.coords.longitude;
        reverseGeocode(lat, lon)
          .done(function (display) { setLocationValue(display); })
          .fail(function () { setLocationValue(lat.toFixed(5) + ', ' + lon.toFixed(5)); });
      }, function () {
        ipFallback().done(setLocationValue)
                    .fail(function () { setLocationValue('Unable to detect location'); });
      }, { timeout: 10000 });
    } else {
      // If browser doesn't support geolocation → fallback to IP
      ipFallback().done(setLocationValue)
                  .fail(function () { setLocationValue('Location not supported'); });
    }
  });
})(jQuery);