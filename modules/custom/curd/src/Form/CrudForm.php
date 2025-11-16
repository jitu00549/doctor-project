<?php

namespace Drupal\curd\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Simple CRUD form with name and mobile.
 */
class CrudForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'curd_crud_form';
  }

  /**
   * Build the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $record = [];
    if ($id) {
      $record = \Drupal::database()->select('curd', 'c')->fields('c')->condition('id', $id)->execute()->fetchAssoc();
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#default_value' => $record['name'] ?? '',
    ];

    $form['mobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile Number'),
      '#maxlength' => 10,
      '#required' => TRUE,
      '#default_value' => $record['mobile'] ?? '',
    ];

    $form['id'] = ['#type' => 'hidden', '#value' => $id];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $id ? $this->t('Update') : $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^[0-9]{10}$/', $form_state->getValue('mobile'))) {
      $form_state->setErrorByName('mobile', $this->t('Please enter a valid 10-digit mobile number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $fields = [
      'name' => $values['name'],
      'mobile' => $values['mobile'],
      'created' => \Drupal::time()->getCurrentTime(),
    ];

    if (!empty($values['id'])) {
      \Drupal::database()->update('curd')->fields($fields)->condition('id', $values['id'])->execute();
      \Drupal::messenger()->addMessage($this->t('Record updated successfully.'));
      $record_id = $values['id'];
    }
    else {
      \Drupal::database()->insert('curd')->fields($fields)->execute();
      $record_id = \Drupal::database()->query('SELECT LAST_INSERT_ID()')->fetchField();
      \Drupal::messenger()->addMessage($this->t('Record saved successfully.'));
    }

    // Send email to site admin
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'curd';
    $key = 'new_record_admin';
    // Send to site email from configuration
    $site_mail = \Drupal::config('system.site')->get('mail');
    $params = [
      'name' => $values['name'],
      'mobile' => $values['mobile'],
      'time' => date('Y-m-d H:i:s'),
    ];
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $mailManager->mail($module, $key, $site_mail, $langcode, $params, NULL, $send);

    $form_state->setRedirect('curd.list');
  }

}
