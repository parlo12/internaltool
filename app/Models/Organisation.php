<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;
    protected $fillable = [
        'organisation_name',
        'calling_service',
        'texting_service' ,
        'signalwire_texting_space_url',
        'signalwire_texting_api_token',
        'signalwire_texting_project_id',
        'twilio_texting_auth_token',
        'twilio_texting_account_sid',
        'twilio_calling_account_sid',
        'twilio_calling_auth_token',
        'signalwire_calling_space_url',
        'signalwire_calling_api_token',
        'signalwire_calling_project_id',
        'user_id',
        'openAI',
        'sending_email',
        'email_password',
        'api_url',
        'auth_token',
        'device_id'

    ];
    
}
