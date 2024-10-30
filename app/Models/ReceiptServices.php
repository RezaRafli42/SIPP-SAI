<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptServices extends Model
{
  protected $table = 'receipt_services';
  protected $guarded = [];
  public $timestamps = false;

  public function receipts()
  {
    return $this->belongsTo(Receipts::class, 'receipt_id');
  }
  public function purchaseOrderServices()
  {
    return $this->belongsTo(PurchaseOrderItems::class, 'purchase_order_service_id');
  }
}
