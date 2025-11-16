<?php
namespace Drupal\simple_contact\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class ContactController extends ControllerBase {
  public function list() {
    $header = ['id' => $this->t('ID'), 'name' => $this->t('Name'), 'mobile' => $this->t('Mobile'), 'operations' => $this->t('Operations')];
    $rows = [];
    $conn = \Drupal::database();
    $result = $conn->select('simple_contact', 's')->fields('s', ['id', 'name', 'mobile'])->execute();
    foreach ($result as $record) {
      $operations['edit'] = ['title' => $this->t('Edit'), 'url' => Url::fromRoute('simple_contact.edit', ['id' => $record->id])];
      $operations['delete'] = ['title' => $this->t('Delete'), 'url' => Url::fromRoute('simple_contact.delete', ['id' => $record->id])];
      $rows[] = ['id' => $record->id, 'name' => $record->name, 'mobile' => $record->mobile,
        'operations' => ['data' => ['#type' => 'operations', '#links' => $operations]]];
    }
    $build['table'] = ['#type' => 'table', '#header' => $header, '#rows' => $rows, '#empty' => $this->t('No contacts found.')];
    $build['add'] = ['#type' => 'link', '#title' => $this->t('Add contact'), '#url' => Url::fromRoute('simple_contact.add'), '#attributes' => ['class' => ['button', 'button--primary']]];
    return $build;
  }
}
