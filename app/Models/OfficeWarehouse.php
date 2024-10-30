<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeWarehouse extends Model
{
  protected $table = 'office_warehouse';
  protected $guarded = [];
  public $timestamps = false;

  public function Items()
  {
    return $this->belongsTo(Items::class, 'item_id', 'id');
  }
}
