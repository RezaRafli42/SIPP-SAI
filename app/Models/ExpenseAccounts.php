<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAccounts extends Model
{
  protected $table = 'expense_accounts';
  protected $guarded = [];
  public $timestamps = false;
}
