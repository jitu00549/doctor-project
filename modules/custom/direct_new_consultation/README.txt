Direct New Consultation - Module
================================

What it does:
- Provides a form at /direct/new_consultation
- Fetches nodes of content type "health_concern" and displays their taxonomy term (field_health_concern_name) and consultant fees (field_consultant_fees).
- On submit shows a confirmation message, auto-scrolls, logs a WhatsApp message and attempts to send via WhatsApp Cloud API if you supply credentials.

Setup:
1. Place this module in modules/custom/direct_new_consultation
2. Enable the module: `drush en direct_new_consultation` or via Drupal admin.
3. Update WhatsApp credentials in src/Form/DirectNewConsultationForm.php:
   - $access_token = 'YOUR_ACCESS_TOKEN_HERE';
   - $phone_number_id = 'YOUR_PHONE_NUMBER_ID_HERE';

Files included:
- direct_new_consultation.info.yml
- direct_new_consultation.routing.yml
- direct_new_consultation.libraries.yml
- src/Form/DirectNewConsultationForm.php
- js/scroll_confirm.js
- README.txt

Notes:
- This module assumes nodes of type 'health_concern' exist and each node has:
  - field_health_concern_name (entity reference to taxonomy term)
  - field_consultant_fees (text or string field containing e.g. "₹499")
- WhatsApp send uses the HTTP request to Meta Graph API. Replace placeholders with real values before enabling send.