<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItems extends Model
{
  protected $table = 'purchase_order_items';
  protected $guarded = [];
  public $timestamps = false;

  public function purchaseOrders()
  {
    return $this->belongsTo(PurchaseOrders::class, 'purchase_order_id');
  }

  public function purchaseRequestItems()
  {
    return $this->belongsTo(PurchaseRequestItems::class, 'purchase_request_item_id');
  }
}
