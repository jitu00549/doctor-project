<?php

namespace Drupal\health_concern_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller for displaying health concern data.
 */
class HealthConcernController extends ControllerBase {

  /**
   * Display all health_concern nodes with selected fields.
   */
  public function display() {
    $header = [
      'name' => $this->t('Health Concern Name'),
      'speciality' => $this->t('Speciality'),
      'fees' => $this->t('Consultant Fees'),
      'link' => $this->t('Link Concern'),
    ];

    $rows = [];

    // Fetch all published nodes of type health_concern.
  $query = \Drupal::entityQuery('node')
  ->condition('type', 'health_concern')
  ->condition('status', 1)
  ->accessCheck(TRUE);
$nids = $query->execute();

    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);

      foreach ($nodes as $node) {
        $name = '';
        $fees = '';
        $link = '';
        $speciality = '';

        // Field: field_health_concern_name
        if ($node->hasField('field_health_concern_name') && !$node->get('field_health_concern_name')->isEmpty()) {
          $name = $node->get('field_health_concern_name')->value;
        }

        // Field: field_consultant_fees
        if ($node->hasField('field_consultant_fees') && !$node->get('field_consultant_fees')->isEmpty()) {
          $fees = $node->get('field_consultant_fees')->value;
        }

        // Field: field_link_concern
        if ($node->hasField('field_link_concern') && !$node->get('field_link_concern')->isEmpty()) {
          $link = $node->get('field_link_concern')->uri ?? $node->get('field_link_concern')->value;
        }

        // Field: field_speciality (taxonomy term reference)
        if ($node->hasField('field_speciality') && !$node->get('field_speciality')->isEmpty()) {
          $term = $node->get('field_speciality')->entity;
          $speciality = $term ? $term->label() : '';
        }

        $rows[] = [
          'name' => $name,
          'speciality' => $speciality,
          'fees' => '₹' . $fees,
          'link' => $link ? "<a href='{$link}' target='_blank'>Visit</a>" : '',
        ];
      }
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No health concerns found.'),
      '#attributes' => ['class' => ['table', 'table-striped', 'table-bordered']],
    ];

    return $build;
  }

}
