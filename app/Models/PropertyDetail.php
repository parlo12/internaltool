<?php

// app/Models/PropertyDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'upa',
        'sca',
        'downpayment',
        'purchase_price',
        'organisation_id',
    ];
}
