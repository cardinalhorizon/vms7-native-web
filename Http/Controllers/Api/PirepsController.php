<?php

namespace Modules\SmartCARSNative\Http\Controllers\Api;

use App\Contracts\Controller;
use Illuminate\Http\Request;

/**
 * class ApiController
 * @package Modules\SmartCARSNative\Http\Controllers\Api
 */
class PirepsController extends Controller
{
    /**
     * Just send out a message
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function details(Request $request)
    {
        return $this->message('Hello, world!');
    }

    /**
     * Handles /hello
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function search(Request $request)
    {
        // Another way to return JSON, this for a custom response
        // It's recommended to use Resources for responses from the public\smartcars\0.2.1\handlers\phpvms5\assets\database
        return response()->json([
            'name' => Auth::user()->name,
        ]);
    }

}
