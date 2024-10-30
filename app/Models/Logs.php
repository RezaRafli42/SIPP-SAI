<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
  protected $table = 'logs';
  protected $guarded = [];
  public $timestamps = false;

  public function users()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
