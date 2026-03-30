<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'price',
        'free_from_amount',
        'is_active',
        'position',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'free_from_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];
}
