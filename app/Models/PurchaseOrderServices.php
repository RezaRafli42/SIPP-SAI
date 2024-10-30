<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderServices extends Model
{
  protected $table = 'purchase_order_services';
  protected $guarded = [];
  public $timestamps = false;

  public function purchaseOrders()
  {
    return $this->belongsTo(PurchaseOrders::class, 'purchase_order_id');
  }

  public function purchaseRequestServices()
  {
    return $this->belongsTo(PurchaseRequestServices::class, 'purchase_request_service_id');
  }

  public function services()
  {
    return $this->belongsTo(Services::class, 'service_id');  // Relasi manual untuk services
  }
}
