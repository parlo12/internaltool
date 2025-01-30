<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextSent extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'contact_id',
        'contact_communication_id',
        'cost',
        'organisation_id',
        'user_id',
        'zipcode',
        'city',
        'state',
        'marketing_channel',
        'sending_number',
        'response'
    ];
}
