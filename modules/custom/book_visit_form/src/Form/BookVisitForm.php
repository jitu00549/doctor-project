<?php

namespace Drupal\book_visit_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

class BookVisitForm extends FormBase {

  public function getFormId() {
    return 'book_visit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attached']['library'][] = 'book_visit_form/custom_styles';
    $form['#attached']['library'][] = 'book_visit_form/modal';

    $form['layout'] = [
      '#type' => 'markup',
      '#markup' => '
      <div id="book-visit-container" class="p-4 mt shadow-sm">
        <div class="row">

          <div class="col-md-6 flow-form-left">
            <div class="p-1 rounded-3 bg-white position-relative">
              <h4 class="book-visit-title mb-3">Consult with a Doctor</h4>

              <div class="mb-3">
                <label class="form-label fw-semibold">Speciality</label>
                <div class="speciality-box border rounded-2 p-2 d-inline-block bg-light-subtle"
                     style="min-width:220px; cursor:pointer;">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="speciality_gynaecology" 
                      checked 
                      disabled>
                    <label class="form-check-label fw-semibold" for="speciality_gynaecology">
                      Gynaecology
                      <span class="badge bg-light text-dark ms-2">₹499</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="edit-name">Patient Name</label>
                <input 
                  type="text" 
                  id="edit-name" 
                  name="name" 
                  class="form-control" 
                  placeholder="Enter patient name for prescriptions" 
                  required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold" for="edit-phone">Mobile number</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <img src="https://flagcdn.com/w20/in.png" alt="IN" width="20" height="14" style="border-radius:2px;">
                  </span>
                  <input 
                    type="text" 
                    id="edit-phone" 
                    name="phone" 
                    class="form-control border-start-0" 
                    placeholder="Enter mobile number" 
                    maxlength="10" 
                    required>
                </div>
                <small class="form-text text-muted">
                  A verification code will be sent to this number.
                </small>
              </div>

              <input type="hidden" name="term_id" value="15">

              <div class="#">
                <button type="submit" id="edit-submit" class="btn btn-primary">Continue</button>
              </div>

            </div>
          </div>

          <div class="col-md-6 mt-5">
            <div class="guiding-slider">
              <div class="guiding-slide">
                <img src="https://www.practo.com/consult/bundles/cwipage/images/qualified_doctors.png" alt="Verified Doctors">
                <h4>Verified Doctors</h4>
              </div>
              <div class="guiding-slide">
                <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-security-v1.png" alt="Private & Secure">
                <h4>Private &amp; Secure</h4>
              </div>
              <div class="guiding-slide">
                <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-chats-v1.png" alt="Free Follow-up">
                <h4>Free Follow-up</h4>
              </div>
            </div>
          </div>

        </div>
      </div>
      ',
      '#allowed_tags' => ['div','h4','label','input','span','small','button','img','style','script'],
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
    $term_id = $form_state->getValue('term_id');

    \Drupal::messenger()->addMessage($this->t('Thank you @name, your appointment request has been submitted.', ['@name' => $name]));

    if (!empty($term_id)) {
      $url = Url::fromUserInput('/direct/new_consultation/' . $term_id);
      $form_state->setRedirectUrl($url);
    }
  }

  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $message = $this->t('<h3>Thank you @name</h3><p>Your appointment request has been received. We will contact you soon.</p>', ['@name' => $name]);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(NULL, Markup::create($message)));
    return $response;
  }

}
