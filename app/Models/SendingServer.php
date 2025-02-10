<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendingServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider',
        'signalwire_space_url',
        'signalwire_api_token',
        'signalwire_project_id',
        'twilio_auth_token',
        'twilio_account_sid',
        'user_id',
        'websockets_api_url',
        'websockets_auth_token',
        'websockets_device_id',
        'organisation_id',
        'purpose',
        'server_name'
    ];
}