<?php

namespace Drupal\curd\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

/**
 * Controller for simple CRUD listing and delete.
 */
class CrudController extends ControllerBase {

  /**
   * List records.
   */
    

    


  public function list() {


      // ✅ Create Add Booking button (styled Drupal button).
    $add_link = Link::fromTextAndUrl($this->t('➕ Add Booking'), Url::fromRoute('curd.add_form'))
      ->toRenderable();
    $add_link['#attributes'] = [
      'class' => ['button', 'button--primary', 'button--small', 'mb-4'],
      'style' => 'float: right; margin-bottom: 15px;',
    ];

    // ✅ Add a title with Add button in header.
    $build['header'] = [
      '#type' => 'markup',
      '#markup' => '<h2 style="margin-bottom:10px;">Booking Records</h2>',
    ];

    $build['add_button'] = $add_link;

    $header = ['ID', 'Name', 'Mobile', 'Operations'];
    $rows = [];

   

    $results = \Drupal::database()->select('curd', 'c')->fields('c')->execute();
    foreach ($results as $row) {
      $edit = Link::fromTextAndUrl('Edit', Url::fromRoute('curd.edit_form', ['id' => $row->id]))->toString();
      $delete = Link::fromTextAndUrl('Delete', Url::fromRoute('curd.delete', ['id' => $row->id]))->toString();
      $rows[] = [
        $row->id,
        $row->name,
        $row->mobile,
        Markup::create($edit . ' | ' . $delete),
      ];
    }


    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records found.'),
    ];
  }

  /**
   * Delete record by id.
   */
  public function delete($id) {
    \Drupal::database()->delete('curd')->condition('id', $id)->execute();
    \Drupal::messenger()->addMessage($this->t('Record deleted successfully.'));
    return $this->redirect('curd.list');
  }

}
