<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class PurchaseRequestServices extends Model
{
  protected $table = 'purchase_request_services';
  protected $guarded = [];
  public $timestamps = false;

  public function services()
  {
    return $this->belongsTo(Services::class, 'service_id');
  }

  public function purchaseRequest()
  {
    return $this->belongsTo(PurchaseRequests::class, 'purchase_request_id');
  }
}
