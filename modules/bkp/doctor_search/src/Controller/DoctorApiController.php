<?php

namespace Drupal\doctor_search\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class DoctorApiController {

  public function getDoctors() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'doctor_profile')
      ->range(0, 20);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $data = [];

    foreach ($nodes as $node) {
      $data[] = [
        'name' => $node->label(),
        'specialization' => $node->get('field_doctor_specialty')->value,
        'location' => $node->get('field_doctor_location')->value,
      ];
    }

    return new JsonResponse($data);
  }
}
