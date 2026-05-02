<?php

namespace App\Models;

use App\Traits\HasLogActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    use HasFactory,HasLogActivity,HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cost_price',
        'sell_price',
        'min_quantity',
        'quantity',
        'category_id',
        'for_maintenance',
    ];

    protected $casts = [
        'for_maintenance' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function movements()
    {
        return $this->hasMany(ProductMovement::class);
    }
}
