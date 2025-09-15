<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ContactImportFailure extends Model
{
    use HasFactory, Prunable;
    protected $fillable = [
'user_id',
        'error',
        'phone',
        'contact_name',
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDay());
    }
}
