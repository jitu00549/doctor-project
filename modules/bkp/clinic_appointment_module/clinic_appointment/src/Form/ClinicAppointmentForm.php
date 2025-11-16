<?php

namespace Drupal\clinic_appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a custom clinic appointment booking form.
 */
class ClinicAppointmentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clinic_appointment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['class'][] = 'clinic-appointment-form';
    $form['#attached']['library'][] = 'clinic_appointment/form_style';

    $form['patient_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient Name'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => $this->t('Enter your full name')],
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => $this->t('Enter your mobile number')],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => FALSE,
    ];

    $form['doctor'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Doctor'),
      '#options' => [
        '' => $this->t('- Select -'),
        'dr_singh' => $this->t('Dr. Singh'),
        'dr_sharma' => $this->t('Dr. Sharma'),
        'dr_gupta' => $this->t('Dr. Gupta'),
      ],
      '#required' => TRUE,
    ];

    // Date & time using Drupal core datetime element.
    $form['appointment_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Appointment Date & Time'),
      '#required' => TRUE,
      '#date_date_element' => 'date',
      '#date_time_element' => 'time',
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason for Visit'),
      '#attributes' => ['placeholder' => $this->t('Describe your concern briefly')],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Appointment'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    if ($phone && strlen(preg_replace('/\D/', '', $phone)) < 10) {
      $form_state->setErrorByName('phone', $this->t('Phone number must be at least 10 digits.'));
    }

    $appointment = $form_state->getValue('appointment_date');
    if (empty($appointment)) {
      $form_state->setErrorByName('appointment_date', $this->t('Please select appointment date and time.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save appointment as a node (optional). Ensure you have a content type named 'Appointment'.
    $patient_name = $form_state->getValue('patient_name');
    $doctor = $form_state->getValue('doctor');
    $phone = $form_state->getValue('phone');
    $email = $form_state->getValue('email');
    $message = $form_state->getValue('message');
    $appointment = $form_state->getValue('appointment_date');

    // Attempt to convert datetime value to a string when possible.
    $appointment_value = '';
    if (is_array($appointment) && isset($appointment['date']) && isset($appointment['time'])) {
      $appointment_value = $appointment['date'] . ' ' . $appointment['time'];
    } elseif (is_string($appointment)) {
      $appointment_value = $appointment;
    }

    try {
      $node = Node::create([
        'type' => 'appointment',
        'title' => $patient_name . ' - ' . $doctor,
        'field_phone' => $phone,
        'field_email' => $email,
        'field_doctor' => $doctor,
        'field_appointment_date' => $appointment_value,
        'field_message' => $message,
      ]);
      $node->save();
    }
    catch (\Exception $e) {
      // Do not break submission if node creation fails; log and continue.
      \Drupal::logger('clinic_appointment')->error('Failed to save appointment node: @msg', ['@msg' => $e->getMessage()]);
    }

    $this->messenger()->addMessage($this->t('Your appointment has been booked successfully!'));
  }

}
