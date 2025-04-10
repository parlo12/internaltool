<?php

namespace App\Http\Controllers;

use App\Http\Resources\AICallResource;
use App\Http\Resources\CallsSentResource;
use App\Http\Resources\CancelledContractsResource;
use App\Http\Resources\ClosedDealResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\executedContractsResource;
use App\Http\Resources\OffersResource;
use App\Http\Resources\TextSentResource;
use App\Http\Resources\TotalContactsResource;
use App\Http\Resources\ValidLeadResource;
use App\Models\AICall;
use App\Models\CallsSent;
use App\Models\CancelledContracts;
use App\Models\ClosedDeal;
use App\Models\Contact;
use App\Models\executedContracts;
use App\Models\offers;
use App\Models\TextSent;
use App\Models\User;
use App\Models\ValidLead;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkflowReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $organisationId = auth()->user()->organisation_id;

        $filter = $request->input('filter') && $request->input('filter') !== 'null' ? $request->input('filter') : "";
        $zipcode = $request->input('zipcode') && $request->input('zipcode') !== 'null' ? $request->input('zipcode') : "";
        $state = $request->input('state') && $request->input('state') !== 'null' ? $request->input('state') : "";
        $city = $request->input('city') && $request->input('city') !== 'null' ? $request->input('city') : "";
        $user = $request->input('agent') && $request->input('agent') !== 'null' ? $request->input('agent') : "";
        $marketing_channel = $request->input('marketing_channel') && $request->input('marketing_channel') !== 'null' ? $request->input('marketing_channel') : "";
        $response = $request->input('response') && $request->input('response') !== 'null' ? $request->input('response') : "";
        $sending_number = $request->input('sending_number') && $request->input('sending_number') !== 'null' ? $request->input('sending_number') : "";
        
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Function to apply filters to queries
        $applyFilter = function ($query) use ($filter, $zipcode, $state, $city, $user) {
            if ($filter) {
                switch ($filter) {
                    case 'today':
                        $query->whereDate('created_at', Carbon::today());
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year);
                        break;
                    case 'last_3_months':
                        $query->whereBetween('created_at', [
                            Carbon::now()->subMonths(3),
                            Carbon::now()
                        ]);
                        break;
                    case 'last_6_months':
                        $query->whereBetween('created_at', [
                            Carbon::now()->subMonths(6),
                            Carbon::now()
                        ]);
                        break;
                    case 'this_year':
                        $query->whereYear('created_at', Carbon::now()->year);
                        break;
                }
            }
            return $query;
        };

        // Apply the filters to the contacts query
        $contactsQuery = $applyFilter(Contact::where('organisation_id', $organisationId))
            ->orderBy($sortField, $sortDirection);

        // Apply the filters and sorting to the other queries
        $textSentQuery = $applyFilter(TextSent::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->when($marketing_channel, function ($query) use ($marketing_channel) {
                $query->where('marketing_channel', $marketing_channel);
            })
            ->when($response, function ($query) use ($response) {
                $query->where('response', $response);
            })
            ->when($sending_number, function ($query) use ($sending_number) {
                $query->where('sending_number', $sending_number);
            })
            ->orderBy($sortField, $sortDirection);

        // $cancelledContractsQuery = $applyFilter(CancelledContracts::where('organisation_id', $organisationId))
        //     ->when($zipcode, function ($query) use ($zipcode) {
        //         $query->where('zipcode', $zipcode);
        //     })
        //     ->when($state, function ($query) use ($state) {
        //         $query->where('state', $state);
        //     })
        //     ->when($city, function ($query) use ($city) {
        //         $query->where('city', $city);
        //     })
        //     ->when($user, function ($query) use ($user) {
        //         $query->where('user_id', $user);
        //     })
        //     ->orderBy($sortField, $sortDirection);
        $callsSentQuery = $applyFilter(CallsSent::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->when($marketing_channel, function ($query) use ($marketing_channel) {
                $query->where('marketing_channel', $marketing_channel);
            })
            ->when($response, function ($query) use ($response) {
                $query->where('response', $response);
            })
            ->when($sending_number, function ($query) use ($sending_number) {
                $query->where('sending_number', $sending_number);
            })
            ->orderBy($sortField, $sortDirection);

        $closedDealQuery = $applyFilter(ClosedDeal::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderBy($sortField, $sortDirection);
        $executedContractsQuery = $applyFilter(ExecutedContracts::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderBy($sortField, $sortDirection);

        $offersQuery = $applyFilter(Offers::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderBy($sortField, $sortDirection);

        $validLeadQuery = $applyFilter(ValidLead::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderBy($sortField, $sortDirection);

            $AICallQuery = $applyFilter(AICall::where('organisation_id', $organisationId))
            ->when($zipcode, function ($query) use ($zipcode) {
                $query->where('zipcode', $zipcode);
            })
            ->when($state, function ($query) use ($state) {
                $query->where('state', $state);
            })
            ->when($city, function ($query) use ($city) {
                $query->where('city', $city);
            })
            ->when($user, function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->orderBy($sortField, $sortDirection);

        // Calculate the total cost for contacts before pagination
        $totalCost = round($textSentQuery->sum('cost')+$AICallQuery->sum('cost') + $callsSentQuery->sum('cost'), 2);

        // Paginate the results
        $textsSent = $textSentQuery->paginate(10)->onEachSide(1);
        $AICalls = $AICallQuery->paginate(10)->onEachSide(1);

        $callsSent = $callsSentQuery->paginate(10)->onEachSide(1);
       // $cancelledContracts = $cancelledContractsQuery->paginate(10)->onEachSide(1);
        $closedDeals = $closedDealQuery->paginate(10)->onEachSide(1);
        $executedContracts = $executedContractsQuery->paginate(10)->onEachSide(1);
        $offers = $offersQuery->paginate(10)->onEachSide(1);
        $validLeads = $validLeadQuery->paginate(10)->onEachSide(1);
        $contacts = $contactsQuery->paginate(10)->onEachSide(1);

        $AICallZipcodes = AICall::select('zipcode')->where('organisation_id', $organisationId)->distinct()->pluck('zipcode')->toArray();
        $callsSentsZipcodes = CallsSent::select('zipcode')->where('organisation_id', $organisationId)->distinct()->pluck('zipcode')->toArray();
        $textSentsZipcodes = TextSent::select('zipcode')->distinct()->pluck('zipcode')->toArray();
        $distinctZipcodes = array_unique(array_merge($callsSentsZipcodes,$AICallZipcodes, $textSentsZipcodes));

        $AICallCities = AICall::select('city')->distinct()->pluck('city')->toArray();
        $callsSentsCities = CallsSent::select('city')->where('organisation_id', $organisationId)->distinct()->pluck('city')->toArray();
        $textSentsCities = TextSent::select('city')->distinct()->pluck('city')->toArray();
        $distinctCities = array_unique(array_merge($callsSentsCities,$AICallCities, $textSentsCities));

        $AICallStates = AICall::select('state')->where('organisation_id', $organisationId)->distinct()->pluck('state')->toArray();
        $callsSentsStates = CallsSent::select('state')->where('organisation_id', $organisationId)->distinct()->pluck('state')->toArray();
        $textSentsStates = TextSent::select('state')->distinct()->pluck('state')->toArray();
        $distinctStates = array_unique(array_merge($callsSentsStates,$AICallStates, $textSentsStates));
       
        $AICallSendingNumbers = AICall::select('sending_number')->where('organisation_id', $organisationId)->distinct()->pluck('sending_number')->toArray();
        $callsSentsSendingNumbers = CallsSent::select('sending_number')->where('organisation_id', $organisationId)->distinct()->pluck('sending_number')->toArray();
        $textSentsSendingNumbers = TextSent::select('sending_number')->distinct()->pluck('sending_number')->toArray();
        $distinctSendingNumbers = array_unique(array_merge($callsSentsSendingNumbers,$AICallSendingNumbers, $textSentsSendingNumbers));

        $agents = User::all();

        return inertia("Workflows/Reports", [
            'success' => session('success'),
            'textsSent' => TextSentResource::collection($textsSent),
            'callsSent' => CallsSentResource::collection($callsSent),
            'AICalls' => AICallResource::collection($AICalls),
            //'cancelledContracts' => CancelledContractsResource::collection($cancelledContracts),
            'closedDeals' => ClosedDealResource::collection($closedDeals),
            'executedContracts' => ExecutedContractsResource::collection($executedContracts),
            'offers' => OffersResource::collection($offers),
            'validLeads' => ValidLeadResource::collection($validLeads),
            'contacts' => ContactResource::collection($contacts),
            'totalCost' => $totalCost, // Include the total cost in the response
            'queryParams' => $request->query() ?: null,
            'zipcodes' => $distinctZipcodes,
            'cities' => $distinctCities,
            'states' => $distinctStates,
            'agents' => $agents,
            'sending_numbers'=>$distinctSendingNumbers
        ]);
    }






    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
