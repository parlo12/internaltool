<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\WrongNumberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('update-lead-status/', [ApiController::class, 'update_lead_status'])->name('update-lead-status');
Route::get('get-message/{phone}', [ApiController::class, 'get_message'])->name('get-message');
Route::get('get-user-and-orgs/', [ApiController::class, 'get_user_and_orgs'])->name('get-user-and-orgs');
Route::get('save-response/{phone}', [ApiController::class, 'save_response'])->name('save_response');
Route::post('get-AI-reply', [ApiController::class, 'get_AI_reply'])->name('get-AI-reply');
Route::post('qualify', [ApiController::class, 'qualify'])->name('qualify');
Route::get('/all-assistants', [ApiController::class, 'all_assistants']);
Route::get('/wake-time/{id}', [ApiController::class, 'get_wake_time']);
Route::get('/wrong-number/{to}', [WrongNumberController::class, 'saveWrongNumber']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
