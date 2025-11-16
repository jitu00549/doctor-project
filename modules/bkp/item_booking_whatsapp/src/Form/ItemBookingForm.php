<?php

namespace Drupal\item_booking_whatsapp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ItemBookingForm extends FormBase {

  public function getFormId() {
    return 'item_booking_whatsapp_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => 'Your Name',
      '#required' => TRUE,
    ];

    $form['item'] = [
      '#type' => 'textfield',
      '#title' => 'Item Name',
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => 'WhatsApp Number (with country code)',
      '#description' => 'Example: 919876543210',
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Book Now',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getValue('name');
    $item = $form_state->getValue('item');
    $phone = $form_state->getValue('phone');

    \Drupal::messenger()->addMessage("Item '$item' booked successfully for $name!");

    $message = urlencode("Hello $name, your booking for '$item' is confirmed! ✅");
    $whatsappUrl = "https://wa.me/$phone?text=$message";

    $response = new RedirectResponse($whatsappUrl);
    $response->send();
  }

}
