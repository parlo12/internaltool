<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactImport extends Model
{
    /** @use HasFactory<\Database\Factories\ImportContactsFactory> */
    use HasFactory;
    protected $table = 'contact_imports';
    protected $fillable = [
        'user_id',
        'mappings',
        'data_file',
        'progress_id',
    ];
}
