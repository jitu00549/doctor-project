<?php

namespace Drupal\custom_booking\Controller;

use Drupal\Core\Controller\ControllerBase;

class BookingController extends ControllerBase {
  public function confirmation($booking_code) {
    $record = \Drupal::database()->select('custom_booking', 'b')
      ->fields('b')
      ->condition('booking_code', $booking_code)
      ->execute()
      ->fetchAssoc();

    if (!$record) {
      return ['#markup' => '<h3>Invalid booking code.</h3>'];
    }

    return [
      '#markup' => '<h2>Booking Confirmed!</h2>' .
        '<p><strong>Name:</strong> ' . htmlspecialchars($record['name'], ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p><strong>Mobile:</strong> ' . htmlspecialchars($record['mobile'], ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p><strong>Email:</strong> ' . htmlspecialchars($record['email'], ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p><strong>Date:</strong> ' . htmlspecialchars($record['date'], ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p><strong>Time:</strong> ' . htmlspecialchars($record['time'], ENT_QUOTES, 'UTF-8') . '</p>' .
        '<p><strong>Service:</strong> ' . ucfirst(htmlspecialchars($record['service'], ENT_QUOTES, 'UTF-8')) . '</p>' .
        '<p><strong>Booking Code:</strong> ' . htmlspecialchars($record['booking_code'], ENT_QUOTES, 'UTF-8') . '</p>',
    ];
  }
}
