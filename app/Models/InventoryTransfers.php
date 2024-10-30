<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransfers extends Model
{
  protected $table = 'inventory_transfers';
  protected $guarded = [];
  public $timestamps = false;

  public function ships()
  {
    return $this->hasMany(Ships::class, 'ship_id');
  }
}
