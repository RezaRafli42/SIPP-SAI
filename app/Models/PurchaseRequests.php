<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequests extends Model
{
  protected $table = 'purchase_requests';
  protected $guarded = [];
  public $timestamps = false;

  public function ships()
  {
    return $this->belongsTo(Ships::class, 'ship_id');
  }

  public function items()
  {
    return $this->hasMany(PurchaseRequestItems::class, 'purchase_request_id');
  }
}
