<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipWarehouseConditions extends Model
{
  protected $table = 'ship_warehouse_conditions';
  protected $guarded = [];
  public $timestamps = false;

  public function shipWarehouses()
  {
    return $this->belongsTo(ShipWarehouses::class, 'ship_warehouse_id');
  }
}
