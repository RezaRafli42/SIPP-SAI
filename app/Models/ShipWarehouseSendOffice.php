<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipWarehouseSendOffice extends Model
{
  protected $table = 'ship_warehouse_send_office';
  protected $guarded = [];
  public $timestamps = false;

  public function shipWarehouseConditions()
  {
    return $this->belongsTo(ShipWarehouseConditions::class, 'ship_warehouse_condition_id');
  }
}
