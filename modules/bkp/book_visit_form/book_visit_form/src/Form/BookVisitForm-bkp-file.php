<?php

namespace Drupal\book_visit_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Render\Markup;

class BookVisitForm extends FormBase {

  public function getFormId() {
    return 'book_visit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {


   $form['#attached']['library'][] = 'book_visit_form/custom_styles';

    // Attach dialog ajax library (ensures modal styles & ajax handlers exist)
   $form['header'] = [
      '#markup' => '<h4 class="book-visit-title">Consult with a Doctor </h4>',
    ];

   // 🧩 Section: Speciality (static or dynamic later)
  $form['speciality'] = [
      '#type' => 'markup',
      '#markup' => '
      <div class="mb-3">
        <label class="form-label fw-semibold">Speciality</label>

        <div class="speciality-box border rounded-2 p-2 d-inline-block bg-light-subtle shadow-sm" 
             style="min-width:220px; cursor:pointer;">
          <div class="form-check">
            <input 
              class="form-check-input" 
              type="checkbox" 
              id="speciality_gynaecology" 
              checked 
              disabled
            >
            <label class="form-check-label fw-semibold" for="speciality_gynaecology">
              Gynaecology
              <span class="badge bg-light text-dark ms-2">₹499</span>
            </label>
          </div>
        </div>
      </div>

      <style>
        /* Hover effect for the bordered box */
        .speciality-box:hover {
          background-color: #f8f9fa;
          box-shadow: 0 0 8px rgba(0,0,0,0.15);
          transition: all 0.2s ease-in-out;
        }
      </style>
      ',
      '#allowed_tags' => ['div', 'label', 'input', 'span', 'style'],
    ];


    $form['#attached']['library'][] = 'book_visit_form/modal';

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient name'),
      '#required' => TRUE,
      '#attributes' => [
    'placeholder' => $this->t('Enter patient name for prescriptions'),
    ],
    ];


   $form['phone'] = [
  '#type' => 'markup',
  '#markup' => '
  <div class="mb-3">
    <label class="form-label fw-semibold" for="edit-phone">Mobile number</label>
    <div class="input-group">
      <span class="input-group-text bg-white border-end-0">
        <img src="https://flagcdn.com/w20/in.png" alt="IN" width="20" height="14" style="border-radius:2px;">
      </span>
      <input 
        type="text" 
        id="edit-phone" 
        name="phone" class="form-control border-start-0" placeholder="Enter mobile number" 
        maxlength="10" 
        required
      >
    </div>
    <small class="form-text text-muted">
      A verification code will be sent to this number.
    </small>
  </div>
  ',
  '#allowed_tags' => ['div', 'label', 'input', 'span', 'img', 'small'],
];
    


    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    if (!empty($phone) && !preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid phone number.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Basic submit: show a message. You can extend this to save to DB or send email.
    $name = $form_state->getValue('name');
    \Drupal::messenger()->addMessage($this->t('Thank you @name, your appointment request has been submitted.', ['@name' => $name]));
  }

  /**
   * Ajax submit callback to replace the form with a confirmation message inside the modal.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    // Emulate processing
    $name = $form_state->getValue('name');
    $message = $this->t('<h3>Thank you @name</h3><p>Your appointment request has been received. We will contact you soon.</p>', ['@name' => $name]);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(NULL, Markup::create($message)));
    return $response;
  }
}
