<?php

namespace Drupal\book_visit_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class BookConsultationController extends ControllerBase {

  public function view($nid) {
    $node = Node::load($nid);
    if (!$node || $node->bundle() !== 'health_concern') {
      return [
        '#markup' => '<p>Invalid health concern selected.</p>',
      ];
    }

    // --- Fetch Fields ---
    $speciality = $node->get('field_speciality')->entity ? $node->get('field_speciality')->entity->label() : '';
    $fees = $node->get('field_consultant_fees')->value ?? '';
    $concern_name = $node->get('field_health_concern_name')->value ?? '';

    // --- Build HTML ---
    $html = '
    <div id="book-visit-container" class="p-4 mt-4 shadow-sm">
      <div class="row">

        <!-- LEFT SIDE: Form -->
        <div class="col-md-6 flow-form-left">
          <div class="p-4 rounded-3 bg-white position-relative">

            <h4 class="book-visit-title mb-3">Consult with a Doctor</h4>

            <!-- Speciality -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Speciality</label>
              <div class="speciality-box border rounded-2 p-2 d-inline-block bg-light-subtle" 
                   style="min-width:220px; cursor:pointer;">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" checked disabled>
                  <label class="form-check-label fw-semibold">
                    ' . $speciality . '
                    <span class="badge bg-light text-dark ms-2">₹' . $fees . '</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Name -->
            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit-name">Patient Name</label>
              <input 
                type="text" 
                id="edit-name" 
                name="name" 
                class="form-control" 
                placeholder="Enter patient name for prescriptions" 
                required>
            </div>

            <!-- Phone -->
            <div class="mb-3">
              <label class="form-label fw-semibold" for="edit-phone">Mobile number</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                  <img src="https://flagcdn.com/w20/in.png" alt="IN" width="20" height="14" style="border-radius:2px;">
                </span>
                <input 
                  type="text" 
                  id="edit-phone" 
                  name="phone" 
                  class="form-control border-start-0" 
                  placeholder="Enter mobile number" 
                  maxlength="10" 
                  required>
              </div>
              <small class="form-text text-muted">
                A verification code will be sent to this number.
              </small>
            </div>

            <!-- Submit -->
            <div class="text-start mt-3">
              <button type="submit" id="edit-submit" class="btn btn-primary">Continue</button>
            </div>

          </div>
        </div>

        <!-- RIGHT SIDE: Info -->
        <div class="col-md-6 mt-5">
          <div class="guiding-slider">
            <div class="guiding-slide text-center">
              <img src="https://www.practo.com/consult/bundles/cwipage/images/qualified_doctors.png" alt="Verified Doctors">
              <h4>Verified Doctors</h4>
            </div>
            <div class="guiding-slide text-center">
              <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-security-v1.png" alt="Private & Secure">
              <h4>Private &amp; Secure</h4>
            </div>
            <div class="guiding-slide text-center">
              <img src="https://www.practo.com/consult/bundles/cwipage/images/ic-chats-v1.png" alt="Free Follow-up">
              <h4>Free Follow-up</h4>
            </div>
          </div>
        </div>
      </div>
    </div>';

    return [
      '#type' => 'markup',
      '#markup' => $html,
      '#allowed_tags' => [
        'div', 'img', 'input', 'span', 'button', 'label', 'small', 'h4',
        'p', 'form', 'strong', 'br', 'b', 'i', 'a',
      ],
      '#attached' => [
        'library' => [
          'book_visit_form/custom_styles',
        ],
      ],
    ];
  }

}
