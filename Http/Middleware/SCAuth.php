<?php

namespace Modules\SmartCARSNative\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $model = User::where('api_key', $token)->first();
        //dd($model);
        if (!is_null($model))
        {
            Auth::setUser($model);
            $request->attributes->add(['pilotID' => $model->id]);
            return $next($request);
        }

        else
            return response()->json(['message' => "Invalid Token"], 401);
    }
}
