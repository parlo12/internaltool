<?php

namespace App\Http\Controllers;

use App\Models\AICall;
use Illuminate\Http\Request;

class AICallController extends Controller
{
    public function index(Request $request)
    {
        $ai_calls = AICall::all();
        return inertia("AI_CALL/Index", [
            'success' => session('success'),
            'error' => session('error'),
            'ai_calls' => $ai_calls
        ]);
    }
}
