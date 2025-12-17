<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Variation extends Model
{
    protected $fillable = [
        'type',
        'value',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variations')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
