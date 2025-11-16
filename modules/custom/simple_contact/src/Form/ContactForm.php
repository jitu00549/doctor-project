<?php
namespace Drupal\simple_contact\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ContactForm extends FormBase {
  public function getFormId() { return 'simple_contact_form'; }
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $record = NULL;
    if ($id) {
      $record = \Drupal::database()->select('simple_contact', 's')->fields('s', ['name','mobile'])->condition('id', $id)->execute()->fetchObject();
    }
    $form['id'] = ['#type'=>'hidden','#value'=>$id ?? ''];
    $form['name'] = ['#type'=>'textfield','#title'=>$this->t('Name'),'#required'=>TRUE,'#default_value'=>$record->name ?? ''];
    $form['mobile'] = ['#type'=>'textfield','#title'=>$this->t('Mobile'),'#required'=>TRUE,'#default_value'=>$record->mobile ?? ''];
    $form['actions']['submit']=['#type'=>'submit','#value'=>$this->t('Save')];
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id=$form_state->getValue('id');$name=$form_state->getValue('name');$mobile=$form_state->getValue('mobile');$conn=\Drupal::database();
    if($id){$conn->update('simple_contact')->fields(['name'=>$name,'mobile'=>$mobile])->condition('id',$id)->execute();}
    else{$conn->insert('simple_contact')->fields(['name'=>$name,'mobile'=>$mobile,'created'=>REQUEST_TIME])->execute();}
    $form_state->setRedirect('simple_contact.list');
  }
}
