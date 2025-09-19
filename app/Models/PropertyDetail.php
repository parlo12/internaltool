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
        'plc',
        'organisation_id',
        'agreed_net_proceeds',
        'remaining_amount_after_ANP',
        'monthly_amount',
    ];
}
