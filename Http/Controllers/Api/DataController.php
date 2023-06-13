<?php

namespace Modules\SmartCARSNative\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\News;
use Illuminate\Http\Request;

/**
 * class ApiController
 * @package Modules\SmartCARSNative\Http\Controllers\Api
 */
class DataController extends Controller
{
    /**
     * Just send out a message
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function aircraft(Request $request)
    {
        if ($request->get('state') === "parked")
            $aircraft = Aircraft::where('state', AircraftState::PARKED)->get();
        else
            $aircraft = Aircraft::all();
        $output = [];
        foreach ($aircraft as $item) {
            $output[] = [
                "id" => $item->id,
                "code" => $item->icao,
                "name" => "{$item->name} ({$item->registration})",
                "serviceCeiling" => "40000",
                "maximumPassengers" => 300,
                "maximumCargo" => 1000,
                "minimumRank" => 0
            ];
        }
        return response()->json($output);
    }
    public function airports(Request $request)
    {
        $airports = Airport::get()->map(function($apt) {
            return [
                'id' => $apt->id,
                'code' => $apt->icao,
                'name' => $apt->name,
                'latitude' => $apt->lat,
                'longitude' => $apt->lon
            ];
        });
        return response()->json($airports);
    }
    public function news(Request $request)
    {
        $news = News::latest()->first();
        return response()->json([
            'title' => $news->subject,
            'body' => $news->body,
            'postedAt' => $news->created_at,
            'postedBy' => "Admin"
        ]);
    }

}
