<?php

namespace App\Http\Controllers;

use App\Models\Ships as Ships;
use App\Models\User as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
  public function indexUser()
  {
    $users = User::orderby('name', 'asc')->get();
    return view('pages.users', [
      'users' => $users,
    ]);
  }
}
