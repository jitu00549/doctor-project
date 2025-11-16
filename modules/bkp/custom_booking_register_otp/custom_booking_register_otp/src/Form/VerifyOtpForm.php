<?php

namespace Drupal\custom_booking_register_otp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class VerifyOtpForm extends FormBase {

  public function getFormId() {
    return 'custom_booking_register_otp_verify_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter OTP'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify OTP'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = \Drupal::request()->getSession();
    $saved_otp = $session->get('otp');
    $entered_otp = $form_state->getValue('otp');

    if ($saved_otp == $entered_otp) {
      $data = $session->get('booking_data');
      \Drupal::messenger()->addMessage($this->t('Booking confirmed for @name on @date at @time.', [
        '@name' => $data['name'],
        '@date' => $data['date'],
        '@time' => $data['time'],
      ]));
      $session->remove('otp');
      $session->remove('booking_data');
    } else {
      \Drupal::messenger()->addError($this->t('Invalid OTP. Please try again.'));
    }
  }
}
