<?php

namespace Drupal\doctor_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

class DoctorSearchController extends ControllerBase {

  public function searchPage(Request $request) {
    $lat = $request->query->get('lat');
    $lng = $request->query->get('lng');
    $specialization = $request->query->get('specialization');

    // Render search form
    $form = \Drupal::formBuilder()->getForm('\Drupal\doctor_search\Form\DoctorSearchForm');

    // Get doctors from service
    $service = \Drupal::service('doctor_search.service');
    $doctors = $service->searchDoctors($lat, $lng, $specialization);

    return [
      '#theme' => 'doctor_search_results',
      '#form' => $form,
      '#doctors' => $doctors,
    ];
  }
}
