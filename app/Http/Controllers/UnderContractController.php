<?php

namespace App\Http\Controllers;

use App\Models\UnderContract;
use Illuminate\Http\Request;

class UnderContractController extends Controller
{
    public function index()
    {
        $underContract = UnderContract::all();
        return inertia("UnderContract/Index", [
            "underContracts" => $underContract,
            'success' => session('success'),
        ]);
    }
}
