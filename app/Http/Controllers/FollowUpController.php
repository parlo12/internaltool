<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\UnderContract;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index()
    {
       // $followUp =FollowUp::where('user_id',auth()->user()->id)->get();
        $followUp =FollowUp::all();
        return inertia("FollowUp/Index", [
            "followUps" => $followUp,
            'success' => session('success'),
        ]);
    }
}
