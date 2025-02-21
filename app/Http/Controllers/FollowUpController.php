<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\UnderContract;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index()
    {
        $followUp =FollowUp::all();
        return inertia("FollowUp/Index", [
            "followUps" => $followUp,
            'success' => session('success'),
        ]);
    }
}
