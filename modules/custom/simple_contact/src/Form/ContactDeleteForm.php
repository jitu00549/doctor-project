<?php
namespace Drupal\simple_contact\Form;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class ContactDeleteForm extends ConfirmFormBase {
  protected $id;
  public function getFormId(){return 'simple_contact_delete_confirm';}
  public function getQuestion(){return $this->t('Are you sure you want to delete this contact?');}
  public function getCancelUrl(){return new Url('simple_contact.list');}
  public function buildForm(array $form, FormStateInterface $form_state, $id=NULL){$this->id=$id;return parent::buildForm($form,$form_state);}
  public function submitForm(array &$form, FormStateInterface $form_state){
    if($this->id){\Drupal::database()->delete('simple_contact')->condition('id',$this->id)->execute();}
    $form_state->setRedirect('simple_contact.list');
  }
}
