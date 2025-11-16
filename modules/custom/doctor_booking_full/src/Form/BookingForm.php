<?php

namespace Drupal\doctor_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Class BookingForm
 */
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
      '#title' => $this->t('Select Appointment Date'),
      '#required' => TRUE,
    ];

    $form['booking_slot'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Time Slot'),
      '#options' => [
        'morning' => $this->t('Morning (9 AM - 12 PM)'),
        'afternoon' => $this->t('Afternoon (1 PM - 4 PM)'),
        'evening' => $this->t('Evening (5 PM - 8 PM)'),
      ],
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Appointment'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['btn', 'btn-success']],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Create content node
    $node = Node::create([
      'type' => 'doctor_booking',
      'title' => $values['patient_name'] . ' - ' . $values['booking_date'],
      'field_patient_name' => $values['patient_name'],
      'field_patient_phone' => $values['patient_phone'],
      'field_booking_date' => $values['booking_date'],
      'field_booking_slot' => $values['booking_slot'],
    ]);
    $node->save();

    // Send WhatsApp message
    $this->sendWhatsAppMessage($values['patient_phone'], $values['booking_date'], $values['booking_slot']);

    $this->messenger()->addStatus($this->t('Appointment booked successfully!'));
    $form_state->setRedirectUrl(Url::fromRoute('doctor_booking.thankyou'));
  }

  private function sendWhatsAppMessage($phone, $date, $slot) {
    $message = "Hello! Your appointment has been booked for $date ($slot). Thank you for choosing us!";
    $encodedMessage = urlencode($message);

    // Replace with your API key from https://www.callmebot.com
    $apiKey = "YOUR_API_KEY";
    $apiUrl = "https://api.callmebot.com/whatsapp.php?phone=$phone&text=$encodedMessage&apikey=$apiKey";

    @file_get_contents($apiUrl);
  }
}
