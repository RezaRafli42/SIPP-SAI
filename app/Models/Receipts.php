<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipts extends Model
{
  protected $table = 'receipts';
  protected $guarded = [];
  public $timestamps = false;

  public function receiptItems()
  {
    return $this->hasMany(ReceiptItems::class, 'receipt_id');
  }

  public function receiptServices()
  {
    return $this->hasMany(ReceiptServices::class, 'receipt_id');
  }
}
