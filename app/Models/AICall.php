<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AICall extends Model
{
    use HasFactory;
    protected $fillable = [
        'call_id',
        'calling_phone',
        'called_phone',
        'call_summary',
    ];
}
