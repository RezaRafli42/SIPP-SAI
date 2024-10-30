<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItems extends Model
{
  protected $table = 'purchase_request_items';
  protected $guarded = [];
  public $timestamps = false;

  public function items()
  {
    return $this->belongsTo(Items::class, 'item_id');
  }

  public function purchaseRequest()
  {
    return $this->belongsTo(PurchaseRequests::class, 'purchase_request_id');
  }
}
