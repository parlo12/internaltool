<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberPool extends Model
{
    use HasFactory;
    protected $fillable=[
        'pool_name',
        'pool_messages',
        'pool_time',
        'pool_time_units',
        'organisation_id',
    ];
    public function numbers()
    {
        return $this->hasMany(Number::class);
    }
}
