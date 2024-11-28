<?php

namespace App\Http\Controllers;

use App\Models\LogActivity\LogActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        if (Auth::guard('api')->check()) {
            LogActivity::create([
                'IDUser' => Auth::guard('api')->user()->id,
            ]);
        }
    }
}
