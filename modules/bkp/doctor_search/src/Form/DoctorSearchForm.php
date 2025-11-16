<?php

namespace Drupal\doctor_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class DoctorSearchForm extends FormBase {

  public function getFormId() {
    return 'doctor_search_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#attributes' => ['placeholder' => 'Enter city (e.g., Delhi, Noida)'],
      '#required' => TRUE,
    ];

    $form['specialization'] = [
      '#type' => 'select',
      '#title' => $this->t('Specialization'),
      '#options' => [
        '' => '- Any -',
        'Dentist' => 'Dentist',
        'Cardiologist' => 'Cardiologist',
        'Orthopedic' => 'Orthopedic',
        'ENT' => 'ENT',
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $location = $form_state->getValue('location');
    $specialization = $form_state->getValue('specialization');

    $url = '/find-doctor?location=' . urlencode($location) . '&specialization=' . urlencode($specialization);
    $form_state->setRedirectUrl(\Drupal\Core\Url::fromUserInput($url));
  }
}
