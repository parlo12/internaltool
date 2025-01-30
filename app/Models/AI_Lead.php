<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AI_Lead extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'zipcode',
        'state',
        'offer',
        'address',
        'gender',
        'lead_score',
        'phone',
    ];
}
