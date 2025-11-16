<?php

namespace Drupal\custom_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BookingForm extends FormBase {

  public function getFormId() {
    return 'custom_booking_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = ['#type' => 'textfield', '#title' => $this->t('Full Name'), '#required' => TRUE];
    $form['mobile'] = ['#type' => 'tel', '#title' => $this->t('Mobile Number'), '#required' => TRUE, '#attributes' => ['maxlength' => 10, 'pattern' => '[0-9]{10}']];
    $form['email'] = ['#type' => 'email', '#title' => $this->t('Email Address'), '#required' => TRUE];
    $form['date'] = ['#type' => 'date', '#title' => $this->t('Date'), '#required' => TRUE];
    $form['time'] = ['#type' => 'textfield', '#title' => $this->t('Preferred Time'), '#required' => TRUE];
    $form['service'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Service'),
      '#required' => TRUE,
      '#options' => [
        'consultation' => $this->t('Consultation'),
        'diagnostics' => $this->t('Diagnostics'),
        'treatment' => $this->t('Treatment'),
      ],
    ];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Send OTP'), '#button_type' => 'primary'];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $otp = rand(100000, 999999);
    $session_id = session_create_id();

    $_SESSION['custom_booking'][$session_id] = [
      'otp' => $otp,
      'data' => [
        'name' => $form_state->getValue('name'),
        'mobile' => $form_state->getValue('mobile'),
        'email' => $form_state->getValue('email'),
        'date' => $form_state->getValue('date'),
        'time' => $form_state->getValue('time'),
        'service' => $form_state->getValue('service'),
      ],
    ];

    $message = "Your booking OTP is: $otp";
    $mobile = $form_state->getValue('mobile');
    // Example SMS API integration (replace with real API)
    // file_get_contents("https://sms-provider.com/api/send?to=$mobile&msg=" . urlencode($message));

    // Send Email.
    $to = $form_state->getValue('email');
    $mailManager = \Drupal::service('plugin.manager.mail');
    $params['message'] = $message;
    $mailManager->mail('custom_booking', 'otp_mail', $to, \Drupal::currentUser()->getPreferredLangcode(), $params);

    $this->messenger()->addMessage($this->t('OTP has been sent to your mobile and email.'));
    $response = new RedirectResponse('/booking/verify/' . $session_id);
    $response->send();
  }
}
