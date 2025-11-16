<?php

namespace Drupal\direct_new_consultation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class DirectNewConsultationForm extends FormBase {

  public function getFormId() {
    return 'direct_new_consultation_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $term_id = NULL) {
    // Attach carousel JS
    $form['#attached']['html_head'][] = [
      [
        '#tag' => 'script',
        '#value' => "
          (function ($, Drupal) {
            Drupal.behaviors.directNewConsultation = {
              attach: function (context, settings) {
                $('#carouselExampleIndicators', context).once('carouselInit').each(function () {
                  $(this).carousel({
                    interval: 3000,
                    ride: 'carousel',
                    wrap: true
                  });
                });
              }
            };
          })(jQuery, Drupal);
        ",
      ],
      'direct_new_consultation_carousel_script',
    ];

    // Default values
    $term_name = 'Gynaecology';
    $term_price = '499';

    if (!empty($term_id)) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);
      if ($term) {
        $term_name = $term->getName();
        if ($term->hasField('field_speciality_price') && !$term->get('field_speciality_price')->isEmpty()) {
          $term_price = $term->get('field_speciality_price')->value;
        }
      }
    }

    // Layout
    $form['layout'] = [
      '#type' => 'markup',
      '#markup' => '
      <div id="book-visit-container" class="p-4 mt shadow-sm">
        <div class="row">
          <div class="col-md-6 flow-form-left">
            <div class="p-3 rounded-3 bg-white position-relative shadow-sm">
              <h4 class="book-visit-title mb-3">Consult with a Docto  admin r</h4>

              <div class="mb-3">
                <label class="form-label fw-semibold">Speciality</label>
                <div class="speciality-box border rounded-2 p-2 d-inline-block bg-light-subtle" style="min-width:220px;">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="speciality_selected" checked disabled>
                    <label class="form-check-label fw-semibold" for="speciality_selected">
                      ' . $term_name . '
                      <span class="badge bg-light text-dark ms-2">₹' . $term_price . '</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="edit-name">Patient Name</label>
                <input type="text" id="edit-name" name="name" class="form-control" placeholder="Enter patient name" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="edit-phone">Mobile number</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <img src="https://flagcdn.com/w20/in.png" alt="IN" width="20" height="14">
                  </span>
                  <input type="text" id="edit-phone" name="phone" class="form-control border-start-0" placeholder="Enter mobile number" maxlength="10" required>
                </div>
                <small class="form-text text-muted">A confirmation message will be sent via WhatsApp.</small>
              </div>

              <input type="hidden" name="term_id" value="' . $term_id . '">
              <button type="submit" id="edit-submit" class="btn btn-primary w-100">Continue</button>
            </div>
          </div>

          <div class="col-md-6 mt-5">
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-inner">
                <div class="carousel-item active text-center">
                  <img src="https://www.practo.com/consult/bundles/cwipage/images/qualified_doctors.png" alt="Verified Doctors">
                  <h4 class="mt-2">Verified Doctors</h4>
                </div>
                <div class="carousel-item text-center">
                  <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-security-v1.png" alt="Private & Secure">
                  <h4 class="mt-2">Private & Secure</h4>
                </div>
                <div class="carousel-item text-center">
                  <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-chats-v1.png" alt="Free Follow-up">
                  <h4 class="mt-2">Free Follow-up</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>',
      '#allowed_tags' => ['div','h4','label','input','span','small','button','img'],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid 10-digit phone number.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $phone = $form_state->getValue('phone');
    $term_id = $form_state->getValue('term_id');

    $term_name = '';
    $term_price = '';

    if (!empty($term_id)) {
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);
      if ($term) {
        $term_name = $term->getName();
        if ($term->hasField('field_speciality_price') && !$term->get('field_speciality_price')->isEmpty()) {
          $term_price = $term->get('field_speciality_price')->value;
        }
      }
    }

    // ✅ Save data
    \Drupal::database()->insert('direct_new_consultation')
      ->fields([
        'name' => $name,
        'phone' => $phone,
        'speciality' => $term_name,
        'price' => $term_price,
        'created' => REQUEST_TIME,
      ])
      ->execute();

    // ✅ Success message
    \Drupal::messenger()->addMessage($this->t('Thank you @name, your booking is confirmed.', ['@name' => $name]));

    // ✅ Send WhatsApp message
    $this->sendWhatsAppMessage($phone, $name, $term_name, $term_price);
  }

  private function sendWhatsAppMessage($phone, $name, $speciality, $price) {
    $token = 'YOUR_WHATSAPP_ACCESS_TOKEN';
    $phone_number_id = 'YOUR_PHONE_NUMBER_ID';

    $message = "Hello $name,\nYour booking for $speciality consultation is confirmed.\nFee: ₹$price\nThank you for choosing our service.";

    $url = "https://graph.facebook.com/v17.0/$phone_number_id/messages";
    $data = [
      "messaging_product" => "whatsapp",
      "to" => "91$phone",
      "type" => "text",
      "text" => ["body" => $message],
    ];

    $options = [
      'headers' => [
        'Authorization' => "Bearer $token",
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode($data),
      'method' => 'POST',
    ];

    try {
      \Drupal::httpClient()->request('POST', $url, $options);
    }
    catch (\Exception $e) {
      \Drupal::logger('direct_new_consultation')->error('WhatsApp send failed: @error', ['@error' => $e->getMessage()]);
    }
  }
}
