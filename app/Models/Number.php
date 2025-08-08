<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone_number',
        'purpose',
        'provider',
        'organisation_id',
        'sending_server_id',
        'number_pool_id',
        'can_refill_on',
        'remaining_messages',
        'redirect_to',
    ];
    public function numberPool()
    {
        return $this->belongsTo(NumberPool::class);
    }
}
