<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveredEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'contact_name',
        'workflow_id',
        'organisation_id',
        'user_id',
        'zipcode',
        'state',
        'city',
        'address',
        'offer',
        'age',
        'gender',
        'lead_score',
        'agent',
        'novation',
        'creative_price',
        'monthly',
        'downpayment',
        'generated_message',
        'list_price',
        'no_second_call',
        'earnest_money_deposit',
        'email',
    ];
}
