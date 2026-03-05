<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'quantity',
        'condition',
        'price',
        'source',
        'description',
    ];
}
