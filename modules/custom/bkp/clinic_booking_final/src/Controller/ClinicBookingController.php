<?php

namespace Drupal\clinic_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Link;

class ClinicBookingController extends ControllerBase {

 public function doctorList() {
  $build = [];

  $doctors = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'find_doctor']);

  $items = [];
  foreach ($doctors as $doctor) {
    $doctor_id = $doctor->id();
    $doctor_name = $doctor->getTitle();
    $address = $doctor->get('field_addree')->value ?? '';
    $fee = $doctor->get('field_consultation_fee_at_clinic')->value ?? '';

    // Dummy image if doctor doesn't have one
    if ($doctor->hasField('field_image') && !$doctor->get('field_image')->isEmpty()) {
      $image_file = $doctor->get('field_image')->entity;
      $photo = file_create_url($image_file->getFileUri());
    } else {
      $photo = '/modules/custom/clinic_booking/images/dummy-doctor.png'; // path to dummy image
    }

    // Dynamic booking link
    $book_link = [
      '#type' => 'link',
      '#title' => $this->t('Book Now'),
      '#url' => \Drupal\Core\Url::fromRoute('clinic_booking.form', ['doctor' => $doctor_id]),
      '#attributes' => ['class' => ['btn', 'btn-primary']],
    ];

    // Star rating (static dummy 4/5)
    $rating = [
      '#markup' => '<div class="doctor-rating">⭐ ⭐ ⭐ ⭐ ☆</div>',
    ];

    // Doctor card container
    $items[] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['doctor-card']],
      'img' => [
        '#theme' => 'image',
        '#uri' => $photo,
        '#attributes' => ['class' => ['doctor-card-img']],
        '#alt' => $doctor_name,
      ],
      'info' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['doctor-info']],
        'name' => ['#markup' => "<h3>{$doctor_name}</h3>"],
        'address' => ['#markup' => "<p>{$address}</p>"],
        'fee' => ['#markup' => "<p>Fee: {$fee}</p>"],
        'rating' => $rating,
        'availability' => ['#markup' => "<p class='available-today'>Available Today</p>"],
        'book' => $book_link,
      ],
    ];
  }

  $build['doctors'] = [
    '#theme' => 'item_list',
    '#items' => $items,
    '#attributes' => ['class' => ['doctor-list']],
  ];

  $build['#attached']['library'][] = 'clinic_booking/clinic_booking_css';

  return $build;
}


}
