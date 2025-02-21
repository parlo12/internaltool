<?php

namespace App\Http\Controllers;

use App\Models\UnderContract;
use Illuminate\Http\Request;

class UnderContractController extends Controller
{
    public function index()
    {
        $underContract = UnderContract::where('user_id',auth()->user()->id)->get();;
        return inertia("UnderContract/Index", [
            "underContracts" => $underContract,
            'success' => session('success'),
        ]);
    }
}
