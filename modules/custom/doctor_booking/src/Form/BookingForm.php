<?php

namespace Drupal\doctor_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class BookingForm extends FormBase {

  public function getFormId() {
    return 'doctor_booking_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['patient_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient Name'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => 'Enter your full name'],
    ];

    $form['patient_phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WhatsApp Number'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => 'Enter your WhatsApp number'],
    ];

    $form['booking_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Select Date'),
      '#required' => TRUE,
    ];

    $form['booking_slot'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Time Slot'),
      '#options' => [
        'morning' => 'Morning (9AM - 12PM)',
        'afternoon' => 'Afternoon (1PM - 4PM)',
        'evening' => 'Evening (5PM - 8PM)',
      ],
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Appointment'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $node = Node::create([
      'type' => 'doctor_booking',
      'title' => $values['patient_name'] . ' - ' . $values['booking_date'],
      'field_patient_name' => $values['patient_name'],
      'field_patient_phone' => $values['patient_phone'],
      'field_booking_date' => $values['booking_date'],
      'field_booking_slot' => $values['booking_slot'],
    ]);
    $node->save();

    $this->sendWhatsAppMessage($values['patient_phone'], $values['booking_date'], $values['booking_slot']);

    $form_state->setRedirect('doctor_booking.thankyou');
  }

  private function sendWhatsAppMessage($phone, $date, $slot) {
    $message = "Your appointment is booked for $date ($slot). Thank you for booking with us!";
    $encodedMessage = urlencode($message);

    $apiUrl = "https://api.callmebot.com/whatsapp.php?phone=$phone&text=$encodedMessage&apikey=YOUR_API_KEY";
    @file_get_contents($apiUrl);
  }
}
