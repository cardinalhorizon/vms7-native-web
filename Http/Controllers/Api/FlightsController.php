<?php

namespace Modules\SmartCARSNative\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Models\Acars;
use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AcarsType;
use App\Models\Enums\PirepSource;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\Subfleet;
use App\Models\User;
use App\Services\BidService;
use App\Services\FlightService;
use App\Services\PirepService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * class ApiController
 * @package Modules\SmartCARSNative\Http\Controllers\Api
 */
class FlightsController extends Controller
{
    public function __construct(public FlightService $flightService, public BidService $bidService, public PirepService $pirepService)
    {
    }

    /**
     * Just send out a message
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function book(Request $request)
    {
        $flight = Flight::find($request->input('flightID'));
        $user = User::find($request->get('pilotID'));
        $bid = $this->bidService->addBid($flight, $user);
        return response()->json(["bidID" => $bid->id]);
    }
    public function bookings(Request $request)
    {
        $bids = $this->bidService->findBidsForUser(User::find($request->get('pilotID')));
        $output = [];

        foreach ($bids as $bid) {
            // Aircraft Array
            $aircraft = [];
            foreach ($bid->flight->subfleets as $subfleet) {
                foreach ($subfleet->aircraft as $acf) {
                    $aircraft[] = $acf['id'];
                }
            }

            $output[] = [
                "bidID" => $bid->id,
                "number" => $bid->flight->flight_number,
                "code" => $bid->flight->airline->code,
                "departureAirport" => $bid->flight->dpt_airport_id,
                "arrivalAirport" => $bid->flight->arr_airport_id,
                "route" => [],
                "flightLevel" => $bid->flight->level,
                "distance" => $bid->flight->distance,
                "departureTime" => $bid->flight->dpt_time,
                "arrivalTime" => $bid->flight->arr_time,
                "flightTime" => $bid->flight->flight_time,
                "daysOfWeek" => $bid->flight->days,
                "flightID" => $bid->flight->id,
                "aircraft" => $aircraft
            ];
        }

        return response()->json($output);
    }
    public function charter(Request $request)
    {
        return $this->message('Hello, world!');
    }
    public function complete(Request $request)
    {
        $client = new Client();
        try {
            $input = $request->all();
            logger($input);
            //Log::error();
            //($request->all());
            $client->request('POST', 'https://discord.com/api/webhooks/1005682336899276891/uz3PysPUB1Ywcx0mOUgiGhQqLej4N-EIb84v0y61SLxTgNN-UWuNYLWsAbh_i6YgpU1Y', [
                'form_params' => [
                    'content' => "Request "//var_dump($input)
                ]
            ]);
            //dd($request);
            $bid = Bid::find($input['bidID']);
            $pirep = Pirep::where([
                'user_id' => $request->get('pilotID'),
                'flight_id' => $bid['flight_id'],
                //'state' => PirepState::IN_PROGRESS
            ])->first();
            Log::info("Found Pirep to close out");
            $pirep->status = PirepStatus::ARRIVED;
            $pirep->state = PirepState::PENDING;
            $pirep->aircraft_id = $input['aircraft'];
            $pirep->landing_rate = $input['landingRate'];
            $pirep->fuel_used = $input['fuelUsed'];
            $pirep->flight_time = $input['flightTime']  * 60;

            foreach ($input['flightData'] as $data) {
                $log_item = new Acars();
                $log_item->type = AcarsType::LOG;
                $log_item->log = $data['message'];
                $pirep->acars_logs()->save($log_item);
            }
            if (!is_null($input['comments']))
            {
                foreach ($input['flightLog'] as $comment) {
                    if (str_contains($comment, " Comment:"))
                    {
                        $pirep->comments()->create([
                            'user_id' => Auth::user()->id,
                            'comment' => $comment
                        ]);
                    }
                }
            }

            $pirep->save();
            $this->pirepService->submit($pirep);



            return response()->json(['pirepID' => $pirep->id]);
        }catch (\Exception $e)
        {
            logger("Exception for SmartCARS Native: ".$e->getMessage()." on line ".$e->getLine()."/r/n".$e->getTraceAsString());
            $client = new Client();

            $client->request('POST', 'https://discord.com/api/webhooks/1005682336899276891/uz3PysPUB1Ywcx0mOUgiGhQqLej4N-EIb84v0y61SLxTgNN-UWuNYLWsAbh_i6YgpU1Y', [
                'form_params' => [
                    'content' => "Exception for SmartCARS Native: ".$e->getMessage()." on line ".$e->getLine()."/r/n"
                ]
            ]);
        }

    }
    public function search(Request $request)
    {
        $output = [];

        $query = [];

        if ($request->has('departureAirport') && $request->query('departureAirport') !== null) {
            $apt = Airport::where('icao', $request->query('departureAirport'))->first();
            if (!is_null($apt))
                $query['dpt_airport_id'] = $apt->id;
        }

        if ($request->has('arrivalAirport') && $request->query('arrivalAirport') !== null) {
            $apt = Airport::where('icao', $request->query('arrivalAirport'))->first();
            if (!is_null($apt))
                $query['arr_airport_id'] = $apt->id;
        }
        if (empty($query)) {
            $flights = Flight::with('subfleets', 'subfleets.aircraft', 'airline')->get()->take(100);
        } else {
            $flights = Flight::where($query)->with('subfleets', 'subfleets.aircraft', 'airline')->get()->take(100);
        }
        foreach ($flights as $bid) {
            $aircraft = [];
            //dd($bid);
            if (is_null($bid->subfleets))
                continue;
            foreach ($bid->subfleets as $subfleet) {
                $aircraft[] = $subfleet->type;
            }
            $output[] = [
                "id" => $bid->id,
                "number" => $bid->flight_number,
                "code" => $bid->airline->code,
                "departureAirport" => $bid->dpt_airport_id,
                "arrivalAirport" => $bid->arr_airport_id,
                "flightLevel" => $bid->level,
                "distance" => $bid->distance,
                "departureTime" => $bid->dpt_time,
                "arrivalTime" => $bid->arr_time,
                "flightTime" => $bid->flight_time,
                "daysOfWeek" => [],
                "type" => "P",
                "subfleets" => $aircraft
            ];
        }

        return response()->json($output);
    }
    public function prefile(Request $request)
    {
        $user = Auth::user();
        $bid = Bid::find($request->input('bidID'));
        logger($request->all());
        $flight = Flight::find($bid->flight_id);

        $attrs = [
            'flight_number' => $flight->flight_number,
            'airline_id' => $flight->airline_id,
            'dpt_airport_id' => $flight->dpt_airport_id,
            'arr_airport_id' => $flight->arr_airport_id,
            'aircraft_id' => $request->input('aircraftID'),
            'flight_id' => $flight->id,
        ];
        // Check if the pirep already exists.
        $existing = Pirep::where(['user_id' => $user->id, 'state' => PirepState::IN_PROGRESS])->first();
        if (is_null($existing)) {
            try {
                $pirep = $this->pirepService->prefile(Auth::user(), $attrs);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            }
            return response()->json($pirep);
        }
        return response()->json($existing);

    }
    public function unbook(Request $request)
    {
        Bid::where(['user_id' => $request->get('pilotID'), 'id' => $request->post('bidID')])->delete();
    }
    public function update(Request $request)
    {
        $client = new Client();
        try {
            $input = $request->all();
            logger($this->phaseToStatus($input['phase']));
            $pilotID = $request->get('pilotID');
            //$bid = Bid::join('pireps', 'bids.flight_id', '=', 'pireps.flight_id')
            //    ->where(['bids.user_id' => $request->get('pilotID'), 'bids.id' => $request->input('bidID')])->first();
            $bid = Bid::find($input['bidID']);
            $pirep = Pirep::where([
                'user_id' => $pilotID,
                'flight_id' => $bid['flight_id'],
                'state' => PirepState::IN_PROGRESS
            ])->first();
            if (is_null($pirep))
            {
                $bid = Bid::find($input['bidID']);
                $pirep = Pirep::fromFlight(Flight::find($bid->flight_id));

                $client->request('POST', 'https://discord.com/api/webhooks/1005682336899276891/uz3PysPUB1Ywcx0mOUgiGhQqLej4N-EIb84v0y61SLxTgNN-UWuNYLWsAbh_i6YgpU1Y', [
                    'form_params' => [
                        'content' => "Creating Flight"
                    ]
                ]);
                $pirep->user()->associate($pilotID);
                $pirep->state = PirepState::IN_PROGRESS;
                $pirep->status = PirepStatus::BOARDING;
                $pirep->source = PirepSource::ACARS;
                $pirep->source_name = "smartCARS 3";
                $pirep->save();
            }
            $pirep->status = $this->phaseToStatus($input['phase']);
            $pirep->save();
            $pirep->acars()->create([
                'status' => $this->phaseToStatus($input['phase']),
                'type' => AcarsType::FLIGHT_PATH,
                'lat' => $input['latitude'],
                'lon' => $input['longitude'],
                'distance' => $input['distanceRemaining'],
                'heading' => $input['heading'],
                'altitude' => $input['altitude'],
                'gs' => $input['groundSpeed']
            ]);
        }catch (\Exception $e)
        {
            $client = new Client();
            $client->request('POST', 'https://discord.com/api/webhooks/1005682336899276891/uz3PysPUB1Ywcx0mOUgiGhQqLej4N-EIb84v0y61SLxTgNN-UWuNYLWsAbh_i6YgpU1Y', [
                'form_params' => [
                    'content' => "Exception for SmartCARS Native: ".$e->getMessage()." on line ".$e->getLine()
                ]
            ]);
        }
    }

    function phaseToStatus(string $phase) {
        switch(strtolower($phase)) {
            case 'boarding':
                return PirepStatus::BOARDING;
            case 'push_back':
                return PirepStatus::PUSHBACK_TOW;
            case 'taxi':
                return PirepStatus::TAXI;
            case 'take_off':
                return PirepStatus::TAKEOFF;
            case 'rejected_take_off':
                return PirepStatus::TAXI;
            case 'climb_out':
                return PirepStatus::INIT_CLIM;
            case 'climb':
                return PirepStatus::ENROUTE;
            case 'cruise':
                return PirepStatus::ENROUTE;
            case 'descent':
                return PirepStatus::APPROACH;
            case 'approach':
                return PirepStatus::APPROACH_ICAO;
            case 'final':
                return PirepStatus::LANDING;
            case 'landed':
                return PirepStatus::LANDED;
            case 'go_around':
                return PirepStatus::APPROACH;
            case 'taxi_to_gate':
                return PirepStatus::LANDED;
            case 'deboarding':
                return PirepStatus::ARRIVED;
            case 'diverted':
                return PirepStatus::DIVERTED;
            default:
                return null;
        }
    }


}
