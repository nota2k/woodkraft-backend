<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    protected $fillable = [
        'title',
        'price',
        'description',
        'reference',
        'materials',
        'dimensions',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function defaultImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_default', true);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(Variation::class, 'product_variations')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function productVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function suggestedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'suggested_products', 'product_id', 'suggested_product_id');
    }
}
