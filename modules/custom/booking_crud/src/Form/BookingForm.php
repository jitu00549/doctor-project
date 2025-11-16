<?php
namespace Drupal\booking_crud\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Mail\MailManagerInterface;

class BookingForm extends FormBase {

  public function getFormId() {
    return 'booking_crud_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $record = [];
    if ($id) {
      $record = \Drupal::database()->select('booking_crud', 'b')
        ->fields('b')
        ->condition('id', $id)
        ->execute()
        ->fetchAssoc();
    }

    $form['name'] = ['#type' => 'textfield', '#title' => 'Full Name', '#required' => TRUE, '#default_value' => $record['name'] ?? ''];
    $form['email'] = ['#type' => 'email', '#title' => 'Email', '#required' => TRUE, '#default_value' => $record['email'] ?? ''];
    $form['phone'] = ['#type' => 'textfield', '#title' => 'Phone Number', '#maxlength' => 10, '#required' => TRUE, '#default_value' => $record['phone'] ?? ''];
    $form['booking_date'] = ['#type' => 'date', '#title' => 'Booking Date', '#required' => TRUE, '#default_value' => $record['booking_date'] ?? ''];
    $form['image'] = ['#type' => 'managed_file', '#title' => 'Upload Image', '#upload_location' => 'public://booking_images/', '#required' => !$id, '#default_value' => isset($record['image']) ? [$record['image']] : NULL];
    $form['id'] = ['#type' => 'hidden', '#value' => $id];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $id ? 'Update Booking' : 'Save Booking', '#button_type' => 'primary'];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^[0-9]{10}$/', $form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid 10-digit phone number.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $image = $values['image'][0] ?? NULL;
    if ($image) { $file = File::load($image); $file->setPermanent(); $file->save(); }
    $fields = ['name'=>$values['name'],'email'=>$values['email'],'phone'=>$values['phone'],'booking_date'=>$values['booking_date'],'image'=>$image ? $file->getFileUri() : '','created'=>REQUEST_TIME];
    if(!empty($values['id'])) { \Drupal::database()->update('booking_crud')->fields($fields)->condition('id',$values['id'])->execute(); \Drupal::messenger()->addMessage("Booking updated successfully!"); } 
    else { \Drupal::database()->insert('booking_crud')->fields($fields)->execute(); \Drupal::messenger()->addMessage("Booking saved successfully!"); }
    $mailManager = \Drupal::service('plugin.manager.mail'); $params['message'] = "Hello ".$values['name'].",\nYour booking on ".$values['booking_date']." is confirmed."; $mailManager->mail('booking_crud','notify',$values['email'],'en',$params,NULL,TRUE);
    $this->sendWhatsAppMessage($values['phone'],$values['name'],$values['booking_date']);
    $form_state->setRedirect('booking_crud.list');
  }

  private function sendWhatsAppMessage($phone,$name,$date) {
    $token='YOUR_WHATSAPP_ACCESS_TOKEN'; $phone_number_id='YOUR_PHONE_NUMBER_ID';
    $message="Hello $name,\nYour booking for date $date is confirmed.\nThank you!";
    $url="https://graph.facebook.com/v17.0/$phone_number_id/messages";
    $data=["messaging_product"=>"whatsapp","to"=>"91$phone","type"=>"text","text"=>["body"=>$message]];
    $options=['headers'=>['Authorization'=>"Bearer $token",'Content-Type'=>'application/json'],'body'=>json_encode($data),'method'=>'POST'];
    try{ \Drupal::httpClient()->request('POST',$url,$options); } catch(\Exception $e){ \Drupal::logger('booking_crud')->error('WhatsApp send failed: @error',['@error'=>$e->getMessage()]); }
  }
}