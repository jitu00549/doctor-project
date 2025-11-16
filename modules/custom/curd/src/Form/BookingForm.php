<?php

namespace Drupal\curd\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Simple booking form with Name and Mobile Number fields.
 */
class BookingForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'curd_form';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $record = [];
    if ($id) {
      $record = \Drupal::database()
        ->select('curd', 'c')
        ->fields('c')
        ->condition('id', $id)
        ->execute()
        ->fetchAssoc();
    }

    // Name field
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#default_value' => $record['name'] ?? '',
    ];

    // Phone number field
    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#maxlength' => 10,
      '#required' => TRUE,
      '#default_value' => $record['phone'] ?? '',
    ];

    // Hidden ID field for edit mode
    $form['id'] = ['#type' => 'hidden', '#value' => $id];

    // Submit button
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $id ? $this->t('Update Booking') : $this->t('Save Booking'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Validate phone number.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^[0-9]{10}$/', $form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid 10-digit mobile number.'));
    }
  }

  /**
   * Submit form data.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $fields = [
      'name' => $values['name'],
      'phone' => $values['phone'],
      'created' => \Drupal::time()->getCurrentTime(),
    ];

    if (!empty($values['id'])) {
      // Update record
      \Drupal::database()->update('curd')->fields($fields)->condition('id', $values['id'])->execute();
      \Drupal::messenger()->addMessage($this->t('Booking updated successfully.'));
    } else {
      // Insert new record
      \Drupal::database()->insert('curd')->fields($fields)->execute();
      \Drupal::messenger()->addMessage($this->t('Booking saved successfully.'));
    }

    // ✅ Send email confirmation
    $to = 'client@example.com'; // यहां क्लाइंट का ईमेल डालें या फिक्स करें
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'curd';
    $key = 'booking_confirmation';
    $params = [
      'message' => 'Hello ' . $values['name'] . ', your booking has been received successfully!',
    ];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    $form_state->setRedirect('curd.list');
  }

}
