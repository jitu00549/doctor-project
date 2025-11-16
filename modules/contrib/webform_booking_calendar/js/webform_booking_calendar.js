(function ($, Drupal) {
  Drupal.behaviors.webformBookingCalendar = {
    attach: function (context, settings) {
      document.addEventListener('DOMContentLoaded', function () {
        if (settings.webformBookingCalendar) {
          Object.keys(settings.webformBookingCalendar).forEach(function (blockId) {
            var config = settings.webformBookingCalendar[blockId];
            var calendarEl = document.getElementById('calendar-' + blockId);

            console.log('Initializing calendar for block:', blockId);
            console.log('Configuration:', config);

            // Initialize FullCalendar.
            var calendar = new FullCalendar.Calendar(calendarEl, {
              initialView: 'dayGridMonth',
              events: function (fetchInfo, successCallback, failureCallback) {
                // Construct the URL.
                var eventsUrl =
                  '/webform-booking-calendar-data/' +
                  config.webform_ids.join(',') +
                  '/' +
                  config.element_names.join(',');

                console.log('Fetching events from URL:', eventsUrl);

                // Fetch events.
                fetch(eventsUrl)
                  .then((response) => {
                    if (!response.ok) {
                      throw new Error('Network response was not ok');
                    }
                    return response.json();
                  })
                  .then((data) => {
                    console.log('Fetched events:', data);

                    // Assign className and extendedProps for each event.
                    const updatedData = data.map((event) => {
                      if (event.webform_id) {
                        event.className = `fc-event-${event.webform_id.replace(/_/g, '-')}`;
                      }

                      // Add extendedProps for tooltip.
                      event.extendedProps = {
                        details: event.details || 'No additional details available.',
                      };

                      return event;
                    });

                    console.log('Updated events with className and extendedProps:', updatedData);

                    successCallback(updatedData); // Pass updated events to FullCalendar.
                  })
                  .catch((error) => {
                    console.error('Error fetching events:', error);
                    failureCallback(error); // Handle errors.
                  });
              },
              eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
              headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay',
              },
              eventClick: function (info) {
                // Open the URL in a new tab if it exists.
                if (info.event.url) {
                  window.open(info.event.url, '_blank');
                }
                info.jsEvent.preventDefault(); // Prevent the default browser behavior.
              },
              eventMouseEnter: function (info) {
                // Create a tooltip with event details.
                var tooltip = document.createElement('div');
                tooltip.className = 'fc-event-tooltip';
                tooltip.style.position = 'absolute';
                tooltip.style.background = '#fff';
                tooltip.style.border = '1px solid #ccc';
                tooltip.style.padding = '5px';
                tooltip.style.borderRadius = '3px';
                tooltip.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                tooltip.style.whiteSpace = 'pre-wrap';
                tooltip.style.zIndex = '1000';
                tooltip.innerText = info.event.extendedProps.details;

                document.body.appendChild(tooltip);

                // Position the tooltip.
                function positionTooltip(event) {
                  tooltip.style.left = event.pageX + 10 + 'px';
                  tooltip.style.top = event.pageY + 10 + 'px';
                }

                document.addEventListener('mousemove', positionTooltip);

                // Remove the tooltip when the mouse leaves.
                info.el.addEventListener('mouseleave', function removeTooltip() {
                  tooltip.remove();
                  info.el.removeEventListener('mouseleave', removeTooltip);
                  document.removeEventListener('mousemove', positionTooltip);
                });
              },
              eventMouseLeave: function () {
                // Ensure no lingering tooltips.
                const tooltips = document.querySelectorAll('.fc-event-tooltip');
                tooltips.forEach((tooltip) => tooltip.remove());
              },
            });

            calendar.render();
          });
        }
      });
    },
  };
})(jQuery, Drupal);
