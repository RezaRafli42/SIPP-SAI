<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrders extends Model
{
  protected $table = 'purchase_orders';
  protected $guarded = [];
  public $timestamps = false;

  public function ships()
  {
    return $this->belongsTo(Ships::class, 'ship_id');
  }

  public function suppliers()
  {
    return $this->belongsTo(Suppliers::class, 'supplier_id');
  }

  public function purchaseOrderItems()
  {
    return $this->hasMany(PurchaseOrderItems::class, 'purchase_order_id');
  }
}
