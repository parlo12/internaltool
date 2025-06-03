<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledMessages extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone',
        'content',
        'workflow_id',
        'type',
        'contact_id',
        'organisation_id',
        'dispatch_time'
    ];
    
}
