<?php

namespace Drupal\clinic_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class ClinicBookingForm extends FormBase {

  public function getFormId() {
    return 'clinic_booking_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $doctor = NULL) {
    $doctor_node = Node::load($doctor);

    $form['doctor_name'] = [
      '#type' => 'textfield',
      '#title' => 'Doctor',
      '#default_value' => $doctor_node->getTitle(),
      '#disabled' => TRUE,
    ];

    $form['doctor_address'] = [
      '#type' => 'textfield',
      '#title' => 'Address',
      '#default_value' => $doctor_node->get('field_addree')->value ?? '',
      '#disabled' => TRUE,
    ];

    $form['patient_name'] = [
      '#type' => 'textfield',
      '#title' => 'Your Name',
      '#required' => TRUE,
    ];

$form['patient_mobile'] = [
  '#type' => 'tel',
  '#title' => 'Mobile Number',
  '#required' => TRUE,
  '#attributes' => ['maxlength' => 10, 'pattern' => '[0-9]{10}'],
];


    $slots = [];
    if ($doctor_node->get('field_clinic_appointment')->value) {
      $slots[$doctor_node->get('field_clinic_appointment')->value] = $doctor_node->get('field_clinic_appointment')->value;
    }
    if ($doctor_node->get('field_clinic_appointment_2')->value) {
      $slots[$doctor_node->get('field_clinic_appointment_2')->value] = $doctor_node->get('field_clinic_appointment_2')->value;
    }

    $form['time_slot'] = [
      '#type' => 'select',
      '#title' => 'Select Time Slot',
      '#options' => $slots,
      '#required' => TRUE,
    ];

    $form['#attached']['library'][] = 'clinic_booking/clinic_booking_css';

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Book Appointment',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $node = Node::create([
      'type' => 'appointment',
      'title' => 'Appointment - ' . $values['patient_name'],
      'field_doctor' => $values['doctor_name'],
      'field_address' => $values['doctor_address'],
      'field_time' => $values['time_slot'],
      'field_mobile' => $values['patient_mobile'],
    ]);

    $node->save();
    \Drupal::messenger()->addMessage($this->t('Appointment booked successfully!'));
  $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput('/welcome'));
  }

}