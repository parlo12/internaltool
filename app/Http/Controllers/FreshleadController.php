<?php

namespace App\Http\Controllers;

use App\Models\Freshlead;
use Illuminate\Http\Request;

class FreshleadController extends Controller
{
    public function index()
    {
       // $followUp =FollowUp::where('user_id',auth()->user()->id)->get();
        $fresh_lead =Freshlead::all();
        return inertia("FreshLead/Index", [
            "freshLeads" => $fresh_lead,
            'success' => session('success'),
        ]);
    }
}
