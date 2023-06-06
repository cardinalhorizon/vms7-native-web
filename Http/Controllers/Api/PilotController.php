<?php

namespace Modules\SmartCARSNative\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\SmartCARSNative\Models\SmartCARS3Session;

/**
 * class ApiController
 * @package Modules\SmartCARSNative\Http\Controllers\Api
 */
class PilotController extends Controller
{
    private function retrieveUserInformation($user) {


        $expiry = time() + 604800;

        $JWTHeader = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
        $JWTPayload = json_encode(array('sub' => $user['pilotid'], 'exp' => $expiry));
        $JWTHeader = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTHeader));
        $JWTPayload = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTPayload));
        $JWTSignature = hash_hmac('sha256', $JWTHeader . '.' . $JWTPayload, uniqid('', true), true);
        $JWTSignature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($JWTSignature));
        $jwt = $JWTHeader . '.' . $JWTPayload . '.' . $JWTSignature;
        //dd($jwt);
        // Insert new session
        $session = new SmartCARS3Session();
        $session->user_id = $user->id;
        $session->session_id = $jwt;
        $session->expiry = $expiry;
        $session->save();

        $pilotIDSetting = setting('pilots_id_length', 4);

        return [
            'dbID' => $user['id'],
            'pilotID' => $user['airline']['icao'] . str_pad($user['pilotid'], $pilotIDSetting, "0", STR_PAD_LEFT),
            'firstName' => explode(' ', $user['name'])[0],
            'lastName' => explode(' ', $user['name'])[1],
            'email' => $user['email'],
            'rank' => $user['rank']['name'],
            'rankImage' => "", // TODO: Add Rank Image
            'rankLevel' => 0,
            'avatar' => "", // TODO: Add Avatar URL
            'session' => $jwt
        ];
    }
    /**
     * Just send out a message
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        SmartCARS3Session::where('expiry', '<', time())->delete();
        //return response()->json(true);
        // Get the User
        if (str_contains($request->query('username'), '@')) {
            $user = User::where('email', $request->query('username'))->with('airline', 'rank')->first();
        } else {
            $user = User::where('pilot_id', $request->query('username'))->with('airline', 'rank')->first();
        }
        if (is_null($user)) {
            return response()->json(['message' => 'The username or password is incorrect'], 401);
        }
        // Check the password
        if(!password_verify($request->input('password'), $user['password'])) {
            return response()->json(['message' => 'The username or password is incorrect'], 401);
        }
        return response()->json($this->retrieveUserInformation($user));
    }

    /**
     * Handles /hello
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function resume(Request $request)
    {
        SmartCARS3Session::where('expiry', '<', time())->delete();
        $session = explode('.', $request->input('session'));
        if(count($session) !== 3)
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        $session[0] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[0])), true);
        $session[1] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[1])), true);
        if($session[0] === null || $session[1] === null)
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        if($session[0]['alg'] !== 'HS256' || $session[0]['typ'] !== 'JWT')
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        if($session[1]['sub'] === null || $session[1]['exp'] === null)
        {
            return response()->json(['message' => 'The session given was not signed by this website'],400 );
        }
        $validSessions = SmartCARS3Session::where(['pilot_id' => $session[1]['sub'], 'expiry' => $session[1]['exp'], 'session' => $request->input('session')]);
        if(count($validSessions) === 0)
        {
            error(401, 'The session given was not valid');
        }
        $user = User::where('pilot_id', $session[1]['sub'])->with('airline','rank')->first();
        // Success if user found

        return response()->json($this->retrieveUserInformation($user));

    }
    public function statistics(Request $request)
    {
        //dd(true);
        return response()->json([
            'hoursFlown' => 0,
            'flightsFlown' => 0,
            'averageLandingRate' => 0,
            'pirepsFiled' => 0,
        ]);
    }
    public function verify(Request $request)
    {
        SmartCARS3Session::where('expiry', '<', time())->delete();
        $session = explode('.', $request->input('session'));
        Log::error($request->all());
        if(count($session) !== 3)
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        $session[0] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[0])), true);
        $session[1] = json_decode(base64_decode(str_replace(array('-', '_', ''), array('+', '/', '='), $session[1])), true);
        if($session[0] === null || $session[1] === null)
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        if($session[0]['alg'] !== 'HS256' || $session[0]['typ'] !== 'JWT')
        {
            return response()->json(['message' => 'The session provided was not in valid JWT format'],400 );
        }
        if($session[1]['sub'] === null || $session[1]['exp'] === null)
        {
            return response()->json(['message' => 'The session given was not signed by this website'],400 );
        }
        $validSessions = SmartCARS3Session::where(['pilot_id' => $session[1]['sub'], 'expiry' => $session[1]['exp'], 'session' => $request->input('session')]);
        if(count($validSessions) === 0)
        {
            error(401, 'The session given was not valid');
        }
        $user = User::where('pilot_id', $session[1]['sub'])->with('airline','rank')->first();
        // Success if user found

        return response()->json($this->retrieveUserInformation($user));
    }

}
