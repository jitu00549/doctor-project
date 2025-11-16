<?php

namespace Drupal\booking_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;

class BookingForm extends FormBase {

  public function getFormId() {
    return 'booking_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#required' => TRUE,
    ];

    $form['day'] = [
      '#type' => 'date',
      '#title' => $this->t('Day'),
      '#required' => TRUE,
    ];

    $form['time_slot'] = [
      '#type' => 'select',
      '#title' => $this->t('Time Slot'),
      '#options' => [
        '10:00 AM - 11:00 AM' => '10:00 AM - 11:00 AM',
        '11:00 AM - 12:00 PM' => '11:00 AM - 12:00 PM',
        '12:00 PM - 01:00 PM' => '12:00 PM - 01:00 PM',
        '02:00 PM - 03:00 PM' => '02:00 PM - 03:00 PM',
      ],
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Now'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Save to custom DB table
    $conn = Database::getConnection();
    $conn->insert('booking_form_data')
      ->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'day' => $form_state->getValue('day'),
        'time_slot' => $form_state->getValue('time_slot'),
        'created' => REQUEST_TIME,
      ])
      ->execute();

    // Also create a node (content type = booking)
    // Ensure fields exist (install hook should have created them)
    $node = Node::create([
      'type' => 'booking',
      'title' => $form_state->getValue('name') . ' - ' . $form_state->getValue('day'),
      'field_email' => $form_state->getValue('email'),
      'field_phone' => $form_state->getValue('phone'),
      // Date field expects full datetime string; form provides YYYY-MM-DD.
      'field_day' => $form_state->getValue('day'),
      'field_time_slot' => $form_state->getValue('time_slot'),
    ]);
    $node->setPublished(FALSE);
    $node->save();

    $this->messenger()->addStatus($this->t('Booking saved successfully.'));
  }
}
