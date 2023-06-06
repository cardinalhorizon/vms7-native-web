<?php

namespace Modules\SmartCARSNative\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\SmartCARSNative\Models\SmartCARS3Session;

/**
 * Class SCAuth
 * @package Modules\SmartCARSNative\Http\Middleware
 */
class SCAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        //dd($token);
        $model = SmartCARS3Session::where('session_id', $token)->first();
        if (!is_null($model))
        {
            $request->attributes->add(['pilotID' => $model->user_id]);
            return $next($request);
        }

        else
            return response()->json(['message' => "Invalid Token"], 401);
    }
}
