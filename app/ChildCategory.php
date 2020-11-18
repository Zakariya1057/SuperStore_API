<?php

namespace App;

use App\Casts\HTMLDecode;
use Illuminate\Database\Eloquent\Model;

class ChildCategory extends Model
{
    protected $casts = [
        'name' => HTMLDecode::class
    ];

    public function products() {
        $product = new Product();
        return $this->hasMany('App\CategoryProduct','child_category_id')
        ->join('products','products.id','category_products.product_id')
        ->select(
            'products.*'
        )->withCasts($product->casts);
    }
}
