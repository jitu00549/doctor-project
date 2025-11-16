<?php

namespace Drupal\new_consultation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

class ConsultationController extends ControllerBase {

  public function view($nid, Request $request) {
    $node = Node::load($nid);

    if (!$node || $node->bundle() !== 'health_concern') {
      return [
        '#markup' => $this->t('Invalid Health Concern ID.'),
      ];
    }

    $title = $node->getTitle();
    $fees = $node->get('field_consultant_fees')->value;
    $speciality = $node->get('field_speciality')->entity ? $node->get('field_speciality')->entity->label() : '';
    $concern = $node->get('field_health_concern_name')->value;

    $form = \Drupal::formBuilder()->getForm('Drupal\\new_consultation\\Form\\ConsultationForm');

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Consultation for @title', ['@title' => $title]),
      '#items' => [
        "Health Concern: $concern",
        "Speciality: $speciality",
        "Consultant Fees: ₹$fees",
      ],
      '#suffix' => \Drupal::service('renderer')->render($form),
    ];
  }
}
