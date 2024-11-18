<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAccountDetails extends Model
{
  protected $table = 'expense_account_details';
  protected $guarded = [];
  public $timestamps = false;

  public function expenseAccounts()
  {
    return $this->belongsTo(ExpenseAccounts::class, 'account_id');
  }
  public function purchaseOrderItems()
  {
    return $this->belongsTo(PurchaseOrderItems::class, 'purchase_order_item_id');
  }
  public function purchaseOrderServices()
  {
    return $this->belongsTo(PurchaseOrderServices::class, 'purchase_order_service_id');
  }
}
