<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    //
    public function status(Request $request)
    {
        return response()->json([
            'message' => 'Fleet API is running successfully',
        ], 200);
    }
}
