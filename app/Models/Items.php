<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
  protected $table = 'items';
  protected $guarded = [];
  public $timestamps = false;

  public function expenseAccounts()
  {
    return $this->belongsTo(ExpenseAccounts::class, 'account_id');
  }

  public function shipWarehouses()
  {
    return $this->hasMany(ShipWarehouses::class, 'item_id');
  }
}
