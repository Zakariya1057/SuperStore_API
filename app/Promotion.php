<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;
use App\Casts\PromotionCalculator;

class Promotion extends Model
{
    public $casts = [
        'name' => HTMLDecode::class,
        'promotion' => PromotionCalculator::class
    ];

    public function products() {
        return $this->hasMany('App\Product','promotion_id')
        ->join('promotions','promotions.id','products.promotion_id')
        ->join('category_products','category_products.product_id','products.id')
        ->join('parent_categories','parent_categories.id','category_products.parent_category_id')
        ->select(
            'products.*',            
            'parent_categories.id as parent_category_id',
            'parent_categories.name as parent_category_name',
            'promotions.id as promotion_id',
            'promotions.name as promotion'
        )->withCasts($this->casts);
    }
}
