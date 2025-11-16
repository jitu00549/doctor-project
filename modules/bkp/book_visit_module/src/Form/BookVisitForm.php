<?php
namespace Drupal\book_visit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

class BookVisitForm extends FormBase {

  public function getFormId() {
    return 'book_visit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {


        $form['header'] = [
        '#markup' => '<h2>Consult with a Doctor</h2>',
        ];

  
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full name'),
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
    $form['doctor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Doctor (optional)'),
    ];
    $form['preferred_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Preferred date'),
    ];
    $form['preferred_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Preferred time (e.g. 10:00 AM)'),
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason / Message'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Consult Now'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    if (!preg_match('/^[0-9\+\-\s]{6,32}$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Please provide a valid phone number.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $summary = "Name: {$values['name']}\nEmail: {$values['email']}\nPhone: {$values['phone']}\nDoctor: {$values['doctor']}\nPreferred date: {$values['preferred_date']}\nPreferred time: {$values['preferred_time']}\n\nMessage:\n{$values['message']}\n";

    if (NodeType::load('appointment')) {
      $node = Node::create([
        'type' => 'appointment',
        'title' => $this->t('Appointment request: @name', ['@name' => $values['name']]),
        'body' => ['value' => $summary, 'format' => 'basic_html'],
      ]);
      $node->setPublished(FALSE);
      $node->save();
      $this->messenger()->addStatus($this->t('Thanks @name — your appointment request has been received.', ['@name' => $values['name']]));
      return;
    }

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'book_visit';
    $key = 'booking_request';
    $to = \Drupal::config('system.site')->get('mail');
    $params['subject'] = $this->t('New booking request from @name', ['@name' => $values['name']]);
    $params['message'] = $summary;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $result = $mailManager->mail($module, $key, $to, $langcode, $params);

    if ($result['result']) {
      $this->messenger()->addStatus($this->t('Thanks @name — your appointment request has been sent.', ['@name' => $values['name']]));
    } else {
      $this->messenger()->addError($this->t('There was a problem sending your request. Please try again later.'));
    }
  }
}
