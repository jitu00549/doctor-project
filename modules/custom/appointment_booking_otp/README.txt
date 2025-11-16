Appointment Booking with OTP - Drupal 10 module
==============================================

Installation:
1. Copy the 'appointment_booking_otp' folder into 'modules/custom/'.
2. Run database updates (drush en appointment_booking_otp -y; drush updb -y) or enable the module via the UI.
3. The module creates a content type 'Appointment' and database table 'appointment_bookings' on install.

Notes:
- Default working days: Monday-Saturday (Sunday disabled).
- Default working hours: 09:00 - 17:00.
- Slot durations available: 30 minutes, 1 hour.
- OTP is displayed as a Drupal message for testing; replace with SMS/WhatsApp API call in production.
