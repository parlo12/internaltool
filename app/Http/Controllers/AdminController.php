<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Number;
use App\Models\Organisation;
use App\Models\SendingServer;
use App\Models\Spintax;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organisationId = auth()->user()->organisation_id;
        $query = User::where(function ($query) {
            $query->where('is_admin', 0)
                ->orWhereNull('organisation_id');
        })->orWhere(function ($query) {
            $query->where('is_admin', 1)
                ->where('organisation_id', auth()->user()->organisation_id);
        });

        $users = $query->get();

        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");
        $users = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->onEachSide(1);
        $query = Spintax::where('organisation_id', $organisationId);
        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");
        $spintaxes = $query->orderBy($sortField, $sortDirection)
            ->paginate(5)
            ->onEachSide(1);
        $query = Number::where('organisation_id', $organisationId);
        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");
        $numbers = $query->orderBy($sortField, $sortDirection)
            ->paginate(50)
            ->onEachSide(1);
        $query = Organisation::query(); // Select all organisations
        $sortField = request()->get("sort_field", 'created_at');
        $sortDirection = request()->get("sort_direction", "desc");
        $organisations = $query->orderBy($sortField, $sortDirection)
            ->paginate(50)
            ->onEachSide(1);
        $query = SendingServer::query(); // Select all organisations
        $sortField = request()->get("sort_field", 'created_at');
        $sortDirection = request()->get("sort_direction", "desc");
        $sending_servers = $query->orderBy($sortField, $sortDirection)
            ->paginate(50)
            ->onEachSide(1);
        $current_org = Organisation::where('id', auth()->user()->organisation_id)->first();
        return inertia("Admin/Index", [
            "users" => UserResource::collection($users),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
            'error' => session('error'),
            "spintaxes" => $spintaxes,
            'numbers' => $numbers,
            'organisations' => $organisations,
            'sendingServers' => $sending_servers,
            'organisation' => $current_org
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function toggleAdmin(User $user)
    {
        // Check if the user is trying to modify their own admin status
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.index')->with('error', "You cannot remove your own admin rights.");
        }
        if (!$user->organisation_id) {
            return redirect()->route('admin.index')->with('error', "Admins must first belong to an Org");
        }
        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->route('admin.index')->with('success', "User table updated successfully");
    }

    public function store_spintax(Request $request)
    {
        $organisationId = auth()->user()->organisation_id;
        $validated_data = $request->validate([
            'content' => 'required|string|max:255',
        ]);
        $spintax = Spintax::create([
            'content' => $validated_data['content'],
            'organisation_id' => $organisationId
        ]);
        return redirect()->route('admin.index')->with('success', "Spintax saved successfully");
    }
    public function delete_spintax($id)
    {
        $spintax = Spintax::find($id);
        $spintax->delete();
        return response()->json([
            'spintax' => $spintax
        ], 200);
    }
    public function store_number(Request $request)
    {
        $organisationId = auth()->user()->organisation_id;
        $validated_data = $request->validate([
            'phone_number' => 'required|string|max:255',
            'number_purpose' => 'required|string|max:255',
            'phone_number_provider' => 'required|string|max:255',
        ]);
        $number = number::create([
            'phone_number' => $validated_data['phone_number'],
            'purpose' => $validated_data['number_purpose'],
            'provider' => $validated_data['phone_number_provider'],
            'organisation_id' => $organisationId
        ]);
        return redirect()->route('admin.index')->with('success', "Number saved successfully");
    }
    public function delete_number($id)
    {
        $number = Number::find($id);
        $number->delete();
        return response()->json([
            'number' => $number
        ], 200);
    }
    public function store_organisation(Request $request)
    {
        $org = Organisation::create([
            'organisation_name' => $request->organisation_name,
            'openAI' => $request->openAI,
            'calling_service' => $request->calling_service,
            'texting_service' => $request->texting_service,
            'signalwire_texting_space_url' => $request->signalwire_texting_space_url,
            'signalwire_texting_api_token' => $request->signalwire_texting_api_token,
            'signalwire_texting_project_id' => $request->signalwire_texting_project_id,
            'twilio_texting_auth_token' => $request->twilio_texting_auth_token,
            'twilio_texting_account_sid' => $request->twilio_texting_account_sid,
            'twilio_calling_account_sid' => $request->twilio_calling_account_sid,
            'twilio_calling_auth_token' => $request->twilio_calling_auth_token,
            'signalwire_calling_space_url' => $request->signalwire_calling_space_url,
            'signalwire_calling_api_token' => $request->signalwire_calling_api_token,
            'signalwire_calling_project_id' => $request->signalwire_calling_project_id,
            'user_id' => auth()->user()->id,
            'email_password' => $request->email_password,
            'sending_email' => $request->sending_email,
            'api_url' => $request->api_url,
            'auth_token' => $request->auth_token,
            'device_id' => $request->device_id

        ]);
        $user = User::find(auth()->user()->id);
        $user->organisation_id = $org->id;
        $user->save();
        return redirect()->route('admin.index')->with('success', "Org created successfuly. You can now add team members");

        // $validated_data = $request->validate([
        //     'organisation_name'=>'string|max:255',
        //     'calling_service'=>'string|max:255',
        //     'texting_service'=>'string|max:255',
        //     'signalwire_texting_space_url'=>'string|max:255',
        //     'signalwire_texting_api_token'=>'string|max:255',
        //     'signalwire_texting_project_id'=>'string|max:255',
        //     'twilio_texting_auth_token'=>'string|max:255',
        //     'twilio_texting_account_sid'=>'string|max:255',
        //     'twilio_calling_account_sid'=>'string|max:255',
        //     'twilio_calling_auth_token'=>'string|max:255',
        //     'signalwire_calling_space_url'=>'string|max:255',
        //     'signalwire_calling_api_token'=>'string|max:255',
        //     'signalwire_calling_project_id'=>'string|max:255',
        // ]);
    }
    public function store_server(Request $request)
    {
        $sending_server = SendingServer::create([
            'server_name' => $request->server_name,
            'service_provider' => $request->service_provider,
            'purpose' => $request->purpose,
            'signalwire_space_url' => $request->signalwire_space_url,
            'signalwire_api_token' => $request->signalwire_api_token,
            'signalwire_project_id' => $request->signalwire_project_id,
            'twilio_auth_token' => $request->twilio_auth_token,
            'twilio_account_sid' => $request->twilio_account_sid,
            'user_id' => auth()->user()->id,
            'websockets_api_url' => $request->websockets_api_url,
            'websockets_auth_token' => $request->websockets_auth_token,
            'websockets_device_id' => $request->websockets_device_id,
            'organisation_id'=>auth()->user()->organisation_id

        ]);
        return redirect()->route('admin.index')->with('success', "Server $sending_server->server_name created successfuly.");
    }
    public function get_org($id)
    {
        $organisation = Organisation::find($id);
        return response()->json([
            'organisation' => $organisation
        ], 200);
    }
    public function get_server($id)
    {
        $sending_server = SendingServer::find($id);
        return response()->json([
            'sendingServer' => $sending_server
        ], 200);
    }
    public function update_organisation(Request $request)
    {
        // Validate the incoming data
        $validatedData = $request->validate([
            'organisation_id' => 'required',
            'organisation_name' => 'required|string|max:255',
            'openAI' => 'required|string|max:255',
            'calling_service' => 'nullable|string|max:255',
            'texting_service' => 'nullable|string|max:255',
            'signalwire_calling_space_url' => 'nullable|string|max:255',
            'signalwire_calling_api_token' => 'nullable|string|max:255',
            'signalwire_calling_project_id' => 'nullable|string|max:255',
            'signalwire_texting_space_url' => 'nullable|string|max:255',
            'signalwire_texting_api_token' => 'nullable|string|max:255',
            'signalwire_texting_project_id' => 'nullable|string|max:255',
            'twilio_texting_account_sid' => 'nullable|string|max:255',
            'twilio_texting_auth_token' => 'nullable|string|max:255',
            'twilio_calling_account_sid' => 'nullable|string|max:255',
            'twilio_calling_auth_token' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'sending_email' => 'nullable|string|max:255',
            'email_password' => 'nullable|string|max:255',
            'api_url' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
            'auth_token' => 'nullable|string|max:255',

        ]);

        // Find the organisation by ID
        $organisation = Organisation::findOrFail($validatedData['organisation_id']);

        // Update the organisation with the validated data
        $organisation->update($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Organisation updated successfully',
            'organisation' => $organisation
        ], 200);
    }
    public function update_user_organisation(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        // Find the user by ID
        $user = User::find($validatedData['user_id']);

        // Update the user's organisation_id
        $user->organisation_id = $validatedData['organisation_id'];
        $user->save();

        // Return a successful JSON response
        return response()->json([
            'success' => true,
            'user' => $user,
        ], 200);
    }
    public function switch_organisation($orgId)
    {
        $user = User::find(auth()->user()->id);
        // Logic to switch organisation for the user
        if ($user->super_admin) {
            $user->organisation_id = $orgId;
            $user->save();
            return response()->json(['message' => 'Organisation switched successfully']);
        } else {
            return response()->json(['error' => 'Organisation switching denied']);
        }
    }

    public function submit_api_key(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->godspeedoffers_api = $request->input('api_key');
        $user->save();

        return response()->json(['success' => 'API Key submitted successfully'], 200);
    }
    public function delete_user($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('admin.index')->with('success', "User Deleted successfuly");
    }
}
