<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseHistory extends Model
{
  protected $table = 'warehouse_history';
  protected $guarded = [];
  public $timestamps = false;

  public function ships()
  {
    return $this->belongsTo(Ships::class, 'ship_id');
  }
  public function items()
  {
    return $this->belongsTo(Items::class, 'item_id');
  }
}
