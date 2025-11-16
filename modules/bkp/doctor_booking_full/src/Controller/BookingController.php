<?php

namespace Drupal\doctor_booking\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class BookingController
 */
class BookingController extends ControllerBase {

  public function thankYou() {
    return [
      '#markup' => '<div class="text-center mt-5"><h2>✅ Appointment Confirmed!</h2><p>We have sent you a WhatsApp confirmation message.</p></div>',
    ];
  }
}
