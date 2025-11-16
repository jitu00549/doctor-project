<?php

namespace Drupal\custom_booking_register_otp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BookingForm extends FormBase {

  public function getFormId() {
    return 'custom_booking_register_otp_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['mobile'] = [
      '#type' => 'tel',
      '#title' => $this->t('Mobile Number'),
      '#required' => TRUE,
      '#attributes' => ['maxlength' => 10, 'pattern' => '[0-9]{10}'],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Booking Date'),
      '#required' => TRUE,
    ];

    $form['time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Preferred Time'),
      '#required' => TRUE,
      '#placeholder' => 'e.g. 10:00 AM',
    ];

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

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Get OTP'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $otp = rand(100000, 999999);
    $session = \Drupal::request()->getSession();
    $session->set('otp', $otp);
    $session->set('booking_data', [
      'name' => $form_state->getValue('name'),
      'mobile' => $form_state->getValue('mobile'),
      'email' => $form_state->getValue('email'),
      'date' => $form_state->getValue('date'),
      'time' => $form_state->getValue('time'),
      'service' => $form_state->getValue('service'),
    ]);

    \Drupal::messenger()->addMessage($this->t('OTP sent to @mobile and @email. (Debug OTP: @otp)', [
      '@mobile' => $form_state->getValue('mobile'),
      '@email' => $form_state->getValue('email'),
      '@otp' => $otp,
    ]));

    (new RedirectResponse('/booking/verify'))->send();
  }
}
