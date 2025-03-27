<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory;
    protected $fillable = [
        'workflow_id',
        'type',
        'content',
        'delay',
        'custom_sending_data',
        'custom_sending',
        'end_time',
        'start_time',
        'days_of_week',
        'batch_size',
        'batch_delay',
        'step_quota_balance',
        'name',
        'offer_expiry',
        'email_subject',
        'generated_message'
    ];
}
