<?php
namespace Drupal\book_visit\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Book visit form that reads a Health Concern node and shows read-only info.
 */
class BookVisitForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_visit_form';
  }

  /**
   * Build the form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int|null $health_concern
   *   Health concern node ID passed in the route.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $health_concern = NULL) {


        $form['header'] = [
        '#markup' => '<h2>Consult with a Doctor</h2>',
        ]; 
    $form['#attached']['library'][] = 'book_visit/book_visit_style';
    $form['#attributes']['class'][] = 'book-visit-wrapper';

    $form['left'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-left']],
    ];

    $form['left']['title'] = [
      '#markup' => '<h2 class="form-title">Consult with a Doctor</h2>',
    ];

    $speciality = 'N/A';
    $price = '0';
    $disease = '';

    if (!empty($health_concern) && is_numeric($health_concern)) {
      $node = Node::load((int) $health_concern);
      if ($node && $node->bundle() === 'health_concern') {
        $speciality = $node->get('field_health_concern_name')->value ?? $speciality;
        $price = $node->get('field_fulldiscount')->value ?? $price;
        // doctor_disease may be text or entity; try value then render summary.
        if ($node->hasField('field_doctor_disease')) {
          $disease = $node->get('field_doctor_disease')->value ?? '';
        }
      }
    }

    // Info card (read-only)
    $form['left']['info'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['info-card']],
      'pill' => [
        '#markup' => '<div class="info-pill"><span class="pill-dot">&#10003;</span><span>' . htmlspecialchars($speciality) . '</span><span class="info-price">₹' . htmlspecialchars($price) . '</span></div>',
      ],
    ];

    // Read-only fields
    $form['left']['fields'] = [
      '#type' => 'container',
    ];

    $form['left']['fields']['patient_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patient name'),
      '#attributes' => ['placeholder' => $this->t('Enter patient name for prescriptions')],
      '#required' => TRUE,
    ];

    $form['left']['fields']['mobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile number'),
      '#attributes' => ['placeholder' => $this->t('Enter mobile number')],
      '#required' => TRUE,
      '#maxlength' => 15,
    ];

    $form['left']['fields']['note'] = [
      '#markup' => '<div class="note">A verification code will be sent to this number.</div>',
    ];

    $form['left']['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#attributes' => ['class' => ['button-primary']],
      ],
    ];

    // Right side: image and free follow-up
    $form['right'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['side-card']],
    ];

    $form['right']['icon'] = [
      '#markup' => '<div class="side-icon">+</div>',
    ];

    $form['right']['text'] = [
      '#markup' => '<div class="side-text">Free Follow-up</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mobile = $form_state->getValue('mobile');
    // basic numeric check for 10 digit numbers (allow country code handled separately if needed)
    if (!preg_match('/^[0-9]{10,15}$/', $mobile)) {
      $form_state->setErrorByName('mobile', $this->t('Please enter a valid mobile number (10-15 digits).'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Basic action: show message. You can extend to create node/email as needed.
    $this->messenger()->addStatus($this->t('Thank you @name. We will contact you on @mobile.', [
      '@name' => $values['patient_name'],
      '@mobile' => $values['mobile'],
    ]));
  }

}
