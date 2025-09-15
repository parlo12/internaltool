<?php

namespace App\Http\Controllers;

use App\Models\ContactImportFailure;
use Illuminate\Http\Request;


class ContactImportFailureController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $failures = ContactImportFailure::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return inertia('Contacts/ContactImportFailures', [
            'failures' => $failures,
            'success' => session('success'),
            'error' => session('error')
        ]);
    }
}
