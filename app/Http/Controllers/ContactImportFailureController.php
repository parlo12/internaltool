<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactImportFailureController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 20);
        $failures = \App\Models\ContactImportFailure::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // If using Inertia or API, return accordingly. Here, return JSON for simplicity.
        return response()->json($failures);
    }
}
