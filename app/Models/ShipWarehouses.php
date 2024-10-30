<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipWarehouses extends Model
{
  protected $table = 'ship_warehouses';
  protected $guarded = [];
  public $timestamps = false;

  public function shipWarehouseConditions()
  {
    return $this->hasMany(ShipWarehouseConditions::class, 'ship_warehouse_id');
  }

  public function items()
  {
    return $this->belongsTo(Items::class, 'item_id');
  }

  public function ships()
  {
    return $this->belongsTo(Ships::class, 'ship_id');
  }
}
