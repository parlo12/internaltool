<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactImportProgress extends Model
{
    /** @use HasFactory<\Database\Factories\ContactImportProgressFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_contacts',
        'imported_contacts',
        'failed_contacts',
        'processed_contacts',
    ];
}
