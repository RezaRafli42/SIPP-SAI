<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipWarehouseUsages extends Model
{
  protected $table = 'ship_warehouse_usages';
  protected $guarded = [];
  public $timestamps = false;

  public function shipWarehouseConditions()
  {
    return $this->belongsTo(ShipWarehouseConditions::class, 'ship_warehouse_condition_id');
  }
}
