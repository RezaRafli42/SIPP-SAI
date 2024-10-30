<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransferItems extends Model
{
  protected $table = 'inventory_transfer_items';
  protected $guarded = [];
  public $timestamps = false;

  public function inventoryTransfers()
  {
    return $this->hasMany(InventoryTransfers::class, 'inventory_transfer_id');
  }

  public function purchaseRequestItems()
  {
    return $this->hasMany(PurchaseRequestItems::class, 'purchase_request_item_id');
  }
}
