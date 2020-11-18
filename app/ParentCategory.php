<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;

class ParentCategory extends Model
{
    // public $visible = ['id', 'name','child_categories'];
    
    protected $casts = [
        'name' => HTMLDecode::class,
        'description' =>  HTMLDecode::class,
    ];

    public function child_categories() {
        return $this->hasMany('App\ChildCategory','child_category_id')->join('child_categories','child_categories.id','category_products.child_category_id');
    }

    public function products() {
        $product = new Product();
        return $this->hasMany('App\CategoryProduct','parent_category_id')
        ->join('products','products.id','category_products.product_id')
        ->select(
            'products.*'
        )->limit(15)->withCasts($product->casts);
    }

}
