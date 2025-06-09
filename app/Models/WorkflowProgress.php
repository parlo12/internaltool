<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'end_time',
        'user_id',
        'progress',
        'processed',
        'total',
        'batch_size',
        'current_batch',
        'current_record',
        'current_record_id',
    ];
}
