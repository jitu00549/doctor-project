<?php

namespace Drupal\appointment_booking_otp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class OtpVerifyForm extends FormBase {

  public function getFormId() {
    return 'appointment_booking_otp_verify_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter OTP'),
      '#required' => TRUE,
    ];

    $form['actions']['verify'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify & Confirm'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = \Drupal::service('session');
    $data = $session->get('appointment_booking_data');

    if (empty($data)) {
      \Drupal::messenger()->addError($this->t('Session expired or no booking data found.'));
      $form_state->setRedirect('appointment_booking_otp.form');
      return;
    }

    if ($data['otp'] == $form_state->getValue('otp')) {
      // Save booking in custom table
      \Drupal::database()->insert('appointment_bookings')
        ->fields([
          'name' => $data['name'],
          'phone' => $data['phone'],
          'doctor' => $data['doctor'],
          'date' => $data['date'],
          'time_slot' => $data['time_slot'],
          'duration' => $data['duration'],
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();

      // Create node in 'appointment' content type for display
      $node = Node::create([
        'type' => 'appointment',
        'title' => $this->t('Appointment: @name (@date)', ['@name' => $data['name'], '@date' => $data['date']]),
        'field_phone' => $data['phone'],
        'field_doctor' => $data['doctor'],
        'field_date' => $data['date'],
        'field_time_slot' => $data['time_slot'],
        'status' => 1,
      ]);
      $node->save();

      \Drupal::messenger()->addMessage($this->t('Appointment confirmed successfully!'));
      $session->remove('appointment_booking_data');

      $form_state->setRedirect('<front>');
    }
    else {
      \Drupal::messenger()->addError($this->t('Invalid OTP. Please try again.'));
    }
  }
}
