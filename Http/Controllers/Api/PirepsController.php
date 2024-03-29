<?php

namespace Modules\SmartCARSNative\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use App\Models\PirepComment;
use App\Models\User;
use Carbon\Carbon;
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
        $pirepID = $request->get('id');
        $user_id = $request->get('pilotID');

        $pirep = Pirep::find($pirepID);
        $pirep->load('comments', 'acars_logs', 'acars');
        $i = 0;
        return response()->json([
            'flightLog' => $pirep->comments->map(function ($a ) { return $a->comment;}),
            'locationData' => $pirep->acars->map(function ($a) {return ['latitude' => $a->lat, 'longitude' => $a->lon, 'heading' => $a->heading];}),
            'flightData' => $pirep->acars_logs->sortBy('created_at')->map(function ($a) use ($i) { $i++; return [
                'eventId' => $a->id,
                'eventTimestamp' => $a->created_at,
                'eventElapsedTime' => $i,
                'eventCondition' => null,
                'message' => $a->log
            ]; })
        ]);

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
        $user = User::find($request->get('pilotID'));
        $user->load('pireps', 'pireps.airline');
        $output_pireps = [];
        foreach ($user->pireps->sortByDesc('created_at') as $pirep) {
            $output_pireps[] = [
                'id' => $pirep->id,
                'submitDate' => Carbon::createFromTimeString($pirep->submitted_at)->toDateString(),
                'airlineCode' => $pirep->airline->icao,
                'route' => [],
                'number' => $pirep->flight_number,
                'distance' => $pirep->planned_distance->getResponseUnits()['mi'],
                'flightType' => $pirep->flight_type,
                'departureAirport' => $pirep->dpt_airport_id,
                'arrivalAirport' => $pirep->arr_airport_id,
                'aircraft' => $pirep->aircraft_id,
                'status' => self::getStatus($pirep->state),
                'flightTime' => $pirep->flight_time / 60,
                'landingRate' => $pirep->landing_rate,
                'fuelUsed' => $pirep->fuel_used->getResponseUnits()['lbs']
            ];
        }
        return response()->json($output_pireps);
    }
    function getStatus($value) {
        switch(intval($value)) {
            case 1:
                return 'Pending';
                break;
            case 2:
                return 'Accepted';
                break;
            case 6:
                return 'Rejected';
                break;
            default:
                return;
                break;
        }
    }
}
