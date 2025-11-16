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
    // Attach dialog ajax library (ensures modal styles & ajax handlers exist)
    $form['#attached']['library'][] = 'book_visit_form/modal';

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
    ];

    $form['preferred_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Preferred Date'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message or Concern'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Appointment'),
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
