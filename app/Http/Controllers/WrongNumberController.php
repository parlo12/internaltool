<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\WrongNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exports\WrongNumbersExport;
use Maatwebsite\Excel\Facades\Excel;

class WrongNumberController extends Controller
{
    public function saveWrongNumber(Request $request, $to)
    {
        $message = $request->query('message');

        // Log the request for debugging
        Log::info("Wrong number API hit", [
            'to' => $to,
            'message' => $message
        ]);
        $contact = Contact::where('phone', $to)->first();
        if($contact){
            WrongNumber::create([
                'phone' => $to,
                'contact_name' => $contact->contact_name,
                'workflow_id' => $contact->workflow_id,
                'organisation_id' => $contact->organisation_id,
                'user_id' => $contact->user_id,
                'zipcode' => $contact->zipcode,
                'state' => $contact->state,
                'city' => $contact->city,
                'address' => $contact->address,
                'offer' => $contact->offer,
                'email' => $contact->email,
                'age' => $contact->age,
                'gender' => $contact->gender,
                'lead_score' => $contact->lead_score,
                'agent' => $contact->agent,
                'novation' => $contact->novation,
                'creative_price' => $contact->creative_price,
                'monthly' => $contact->monthly,
                'downpayment' => $contact->downpayment,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => "wrong number for {$to} saved"
        ]);
    }
    public function index()
    {
        $wrongNumbers = WrongNumber::where('user_id',auth()->user()->id);
        return inertia("WrongNumbers/Index", [
            "wrongNumbers" => $wrongNumbers,
            'success' => session('success'),
        ]);
    }
   

public function export()
{
    return Excel::download(new WrongNumbersExport, 'wrong_numbers.xlsx');
}
}
