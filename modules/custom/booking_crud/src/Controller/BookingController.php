<?php
namespace Drupal\booking_crud\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

class BookingController extends ControllerBase {

  public function list() {
    $header = ['ID','Name','Email','Phone','Date','Image','Operations'];
    $rows = [];
    $results = \Drupal::database()->select('booking_crud','b')->fields('b')->execute();
    foreach($results as $data){
      $edit=Link::fromTextAndUrl('Edit',Url::fromRoute('booking_crud.edit',['id'=>$data->id]))->toString();
      $delete=Link::fromTextAndUrl('Delete',Url::fromRoute('booking_crud.delete',['id'=>$data->id]))->toString();
      $rows[]=[$data->id,$data->name,$data->email,$data->phone,$data->booking_date,$data->image ? '<img src="'.file_create_url($data->image).'" width="60">' : '',$edit.' | '.$delete];
    }
    return ['#type'=>'table','#header'=>$header,'#rows'=>$rows,'#empty'=>'No bookings found','#allowed_tags'=>['img','a']];
  }

  public function delete($id){
    \Drupal::database()->delete('booking_crud')->condition('id',$id)->execute();
    \Drupal::messenger()->addMessage('Booking deleted successfully.');
    return $this->redirect('booking_crud.list');
  }
}