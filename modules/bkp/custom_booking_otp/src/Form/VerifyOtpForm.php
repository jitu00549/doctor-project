<?php

namespace Drupal\custom_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerifyOtpForm extends FormBase {

  public function getFormId() {
    return 'verify_otp_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $session_id = NULL) {
    $form['session_id'] = ['#type' => 'hidden', '#value' => $session_id];
    $form['otp'] = ['#type' => 'textfield', '#title' => $this->t('Enter OTP'), '#required' => TRUE, '#attributes' => ['maxlength' => 6]];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Verify OTP'), '#button_type' => 'primary'];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session_id = $form_state->getValue('session_id');
    $entered_otp = $form_state->getValue('otp');

    if (isset($_SESSION['custom_booking'][$session_id])) {
      $stored = $_SESSION['custom_booking'][$session_id];

      if ($entered_otp == $stored['otp']) {
        $data = $stored['data'];
        $booking_code = strtoupper(substr(md5(time() . rand()), 0, 8));

        \Drupal::database()->insert('custom_booking')
          ->fields([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'date' => $data['date'],
            'time' => $data['time'],
            'service' => $data['service'],
            'booking_code' => $booking_code,
            'created' => REQUEST_TIME,
          ])->execute();

        $mailManager = \Drupal::service('plugin.manager.mail');
        $params['message'] = "Your booking is confirmed. Booking code: $booking_code";
        $mailManager->mail('custom_booking', 'confirm_mail', $data['email'], \Drupal::currentUser()->getPreferredLangcode(), $params);

        unset($_SESSION['custom_booking'][$session_id]);
        $this->messenger()->addStatus($this->t('Booking confirmed successfully.'));
        $response = new RedirectResponse('/booking/confirmation/' . $booking_code);
        $response->send();
      } else {
        $this->messenger()->addError($this->t('Invalid OTP, please try again.'));
      }
    }
  }
}
