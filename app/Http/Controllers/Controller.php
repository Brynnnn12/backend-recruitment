<?php

namespace App\Http\Controllers;

// 1. Import BaseController dari Laravel
use Illuminate\Routing\Controller as BaseController;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

// 2. Tambahkan "extends BaseController" di sini
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;
}
