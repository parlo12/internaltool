<?php

namespace App\Http\Controllers;

use App\Models\WrongNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        // Example: Update a database record if needed
        // Contact::where('phone', $to)->update(['is_wrong_number' => true]);

        return response()->json([
            'success' => true,
            'message' => "Received wrong number report for {$to}"
        ]);
    }
    public function index()
    {
        $wrongNumbers = WrongNumber::all();
        return inertia("WrongNumbers/Index", [
            "wrongNumbers" =>$wrongNumbers,
            'success' => session('success'),
        ]);
    }
}
