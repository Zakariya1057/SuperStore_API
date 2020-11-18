<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;
use App\Casts\Image;
use App\Casts\PromotionCalculator;

class Product extends Model
{
    public $casts = [
        'name' => HTMLDecode::class,
        'price' => 'double',
        'old_price' => 'double',
        'avg_rating' => 'double',
        'brand' => HTMLDecode::class,

        'description' => HTMLDecode::class,
        'allergen_info' => HTMLDecode::class,
        'dietary_info' => HTMLDecode::class,

        'large_image' => Image::class,
        'small_image' => Image::class,

        'parent_category_name' => HTMLDecode::class,
        'child_category_name' => HTMLDecode::class,

        'promotion' => PromotionCalculator::class
    ];

    public function ingredients() {
        return $this->hasMany('App\Ingredient');
    }

    public function reviews() {
        return $this->hasMany('App\Review')->orderBy('reviews.created_at','DESC')->limit(1);
    }

    public function promotion(){
        return $this->belongsTo('App\Promotion');
    }

}
