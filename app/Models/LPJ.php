<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LPJ extends Model
{
  protected $table = 'lpj';
  protected $guarded = [];
  public $timestamps = false;

  public function purchaseOrders()
  {
    return $this->hasMany(PurchaseOrders::class, 'lpj_id');
  }
}
