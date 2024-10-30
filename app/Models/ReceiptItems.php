<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptItems extends Model
{
  protected $table = 'receipt_items';
  protected $guarded = [];
  public $timestamps = false;

  public function receipts()
  {
    return $this->belongsTo(Receipts::class, 'receipt_id');
  }
  public function purchaseOrderItems()
  {
    return $this->belongsTo(PurchaseOrderItems::class, 'purchase_order_item_id');
  }
}
