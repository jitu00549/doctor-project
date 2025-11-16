Clinic Appointment - Drupal 10 custom module
============================================
Install:
 1. Copy the 'clinic_appointment' folder into your Drupal installation at:
    web/modules/custom/clinic_appointment
 2. Clear cache: drush cr
 3. Enable the module: drush en clinic_appointment -y
 4. Visit: /book-appointment

Notes:
 - The module's form saves submissions as nodes of content type 'appointment'.
 - Create a content type named 'Appointment' with fields matching:
    field_phone (Text), field_email (Email), field_doctor (Text/List), field_appointment_date (Date), field_message (Long text).
 - Adjust field machine names in src/Form/ClinicAppointmentForm.php if you prefer different names.
