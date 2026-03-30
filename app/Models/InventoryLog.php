<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'date',
        'recipient',
        'items',
        'flow',
        'proof_file'
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
