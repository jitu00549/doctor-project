booking_form Drupal 10 module

Installation:
1. Upload the booking_form.zip via Extend -> Install new module OR extract into /modules/custom/booking_form
2. Enable the module (Admin -> Extend).
3. On module installation the module will:
   - create a DB table 'booking_form_data'
   - create a content type 'booking'
   - create fields: field_email, field_phone, field_day, field_time_slot
   - grant 'access booking form' permission to authenticated users
4. Visit /booking/form to see the form and /booking/list to see saved entries.

Notes:
- Nodes created by form are saved as unpublished. You can change that in code.
- If your site uses strict permissions, go to People -> Roles and adjust 'access booking form' permission.
- Test on a local/dev site first.
