<?php

namespace Drupal\appointment_booking_otp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\appointment_booking_otp\Service\SlotGenerator;

class AppointmentForm extends FormBase {
  protected $slotGenerator;

  public function __construct(SlotGenerator $slotGenerator) {
    $this->slotGenerator = $slotGenerator;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('appointment_booking_otp.slot_generator')
    );
  }

  public function getFormId() {
    return 'appointment_booking_otp_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog';

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient Name'),
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => '10-digit mobile number'],
    ];

    $form['doctor'] = [
      '#type' => 'select',
      '#title' => $this->t('Doctor'),
      '#options' => [
        'dr_mehta' => 'Dr. Mehta',
        'dr_sharma' => 'Dr. Sharma',
      ],
      '#required' => TRUE,
    ];

    $today = date('Y-m-d');
    $max_date = date('Y-m-d', strtotime('+90 days'));

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Appointment Date'),
      '#required' => TRUE,
      '#min' => $today,
      '#max' => $max_date,
      '#ajax' => [
        'callback' => '::updateSlots',
        'wrapper' => 'time-slot-wrapper',
      ],
    ];

    $form['duration'] = [
      '#type' => 'select',
      '#title' => $this->t('Duration'),
      '#options' => [
        '30' => $this->t('30 Minutes'),
        '60' => $this->t('1 Hour'),
      ],
      '#default_value' => '30',
      '#ajax' => [
        'callback' => '::updateSlots',
        'wrapper' => 'time-slot-wrapper',
      ],
    ];

    $form['time_slot'] = [
      '#type' => 'select',
      '#title' => $this->t('Available Time Slot'),
      '#options' => $this->getSlots($form_state),
      '#prefix' => '<div id="time-slot-wrapper">',
      '#suffix' => '</div>',
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send OTP'),
    ];

    return $form;
  }

  public function updateSlots(array &$form, FormStateInterface $form_state) {
    return $form['time_slot'];
  }

  protected function getSlots(FormStateInterface $form_state) {
    $date = $form_state->getValue('date') ?: $this->getRequest()->query->get('date');
    $duration = $form_state->getValue('duration') ?: 30;
    if (empty($date) || empty($duration)) {
      return [];
    }
    // Default working hours
    $start_time = '09:00';
    $end_time = '17:00';
    return $this->slotGenerator->getAvailableSlots($date, $start_time, $end_time, (int)$duration);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Prevent selecting Sunday (weekday 0)
    $date = $form_state->getValue('date');
    if ($date) {
      $w = date('w', strtotime($date)); // 0 (Sunday) - 6 (Saturday)
      if ($w == 0) {
        $form_state->setErrorByName('date', $this->t('Sunday is not a working day. Please choose another date.'));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $otp = rand(100000, 999999);
    $session = \Drupal::service('session');

    $session->set('appointment_booking_data', [
      'name' => $form_state->getValue('name'),
      'phone' => $form_state->getValue('phone'),
      'doctor' => $form_state->getValue('doctor'),
      'date' => $form_state->getValue('date'),
      'time_slot' => $form_state->getValue('time_slot'),
      'duration' => $form_state->getValue('duration'),
      'otp' => $otp,
    ]);

    // In production replace this with real SMS/WhatsApp API call.
    \Drupal::messenger()->addMessage($this->t('OTP sent (test): @otp', ['@otp' => $otp]));

    $form_state->setRedirect('appointment_booking_otp.verify_otp');
  }
}
