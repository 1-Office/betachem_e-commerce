<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'specifications',
        'price',
        'stock_quantity',
        'minimum_stock',
        'weight',
        'main_image',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'weight' => 'decimal:2',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
