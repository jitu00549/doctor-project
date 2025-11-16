<?php

namespace Drupal\booking_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class BookingController extends ControllerBase {

  public function list() {
    $header = ['ID', 'Name', 'Email', 'Phone', 'Day', 'Time Slot'];

    $query = Database::getConnection()->select('booking_form_data', 'b')
      ->fields('b', ['id', 'name', 'email', 'phone', 'day', 'time_slot']);
    $results = $query->execute()->fetchAll();

    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        'data' => [
          $row->id,
          $row->name,
          $row->email,
          $row->phone,
          $row->day,
          $row->time_slot,
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No bookings yet.'),
    ];
  }
}
