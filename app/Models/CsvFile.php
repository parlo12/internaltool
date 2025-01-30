<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvFile extends Model
{
    use HasFactory;
    protected $fillable = [
        'original_filename',
        'wireless_path',
        'landline_only_path',
        'processed_landline_path',
        'no_usage_path'
    ];

}
