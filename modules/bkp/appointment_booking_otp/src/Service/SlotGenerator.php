<?php

namespace Drupal\appointment_booking_otp\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\DateFormatterInterface;

class SlotGenerator {
  protected $database;
  protected $timeService;

  public function __construct(Connection $database, $time_service) {
    $this->database = $database;
    $this->timeService = $time_service;
  }

  /**
   * Generate available slots for a given date, start/end time and interval in minutes.
   *
   * @param string $date YYYY-MM-DD
   * @param string $start_time '09:00'
   * @param string $end_time '17:00'
   * @param int $interval minutes (30 or 60)
   *
   * @return array
   */
  public function getAvailableSlots($date, $start_time = '09:00', $end_time = '17:00', $interval = 30) {
    $slots = [];

    try {
      $start = new DrupalDateTime($date . ' ' . $start_time);
      $end = new DrupalDateTime($date . ' ' . $end_time);
    } catch (\Exception $e) {
      return $slots;
    }

    while ($start < $end) {
      $slot_start = $start->format('H:i');
      $slot_end_dt = clone $start;
      $slot_end_dt->modify('+' . $interval . ' minutes');
      $slot_end = $slot_end_dt->format('H:i');

      if ($slot_end_dt > $end) {
        break;
      }

      $slot_label = $slot_start . ' - ' . $slot_end;
      // Check DB for existing booking
      $query = $this->database->select('appointment_bookings', 'a')
        ->fields('a', ['id'])
        ->condition('date', $date)
        ->condition('time_slot', $slot_label);
      $booked = $query->execute()->fetchField();

      if (!$booked) {
        $slots[$slot_label] = $slot_label;
      }

      $start = $slot_end_dt;
    }

    return $slots;
  }
}
