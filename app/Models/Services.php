<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
  protected $table = 'services';
  protected $guarded = [];
  public $timestamps = false;

  public function expenseAccounts()
  {
    return $this->belongsTo(ExpenseAccounts::class, 'account_id');
  }
}
