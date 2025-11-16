# Book Visit Form (Drupal 10)

Module provides a simple modal booking form (5 fields) that can be opened using a Views rewrite link:

Example link in a Views rewrite:

<a href="/book-visit-form" class="use-ajax" data-dialog-type="modal" data-dialog-options='{"width":600}'>Book Clinic Visit</a>

Installation:
1. Copy `book_visit_form` folder to `web/modules/custom/` (or your site's modules/custom).
2. Run `drush en book_visit_form -y` or enable via Extend UI.
3. Ensure your theme or page attaches the `book_visit_form/modal` library if necessary (core/drupal.dialog.ajax is the main dependency).

Customization:
- Modify `src/Form/BookVisitForm.php` to change fields, validation, or submission handling (save, email, etc).
