<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'category_id',
        'uuid',
        'name',
        'slug',
        'description',
        'images',
        'price',
        'currency',
        'stock_quantity',
        'unit',
        'attributes',
        'status',
        'is_verified',
        'rating_avg',
        'rating_count',
    ];

    protected $casts = [
        'images' => 'array',
        'attributes' => 'array',
        'price' => 'decimal:2',
        'rating_avg' => 'decimal:1',
        'is_verified' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($product->slug)) {
                $product->slug = \Illuminate\Support\Str::slug($product->name) . '-' . uniqid();
            }
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->name,
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}