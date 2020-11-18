<?php

namespace App\Traits;

use App\MonitoredProduct;
use App\Product;

trait MonitoringTrait {

    protected function monitoring_products($user_id){
        $product = new Product();

        $products =  MonitoredProduct::where([ ['user_id', $user_id] ])
        ->select('products.*' ,'parent_categories.id as parent_category_id', 'parent_categories.name as parent_category_name')
        ->join('products','products.id','monitored_products.product_id')
        ->join('category_products','category_products.product_id','products.id')
        ->join('parent_categories','category_products.parent_category_id','parent_categories.id')
        ->withCasts(
            $product->casts
        )->limit(20)->groupBy('products.id')->orderBy('monitored_products.created_at','DESC')->get();


        foreach($products as $product_item){
            $product_item->monitoring = true;
        }

        return $products;

    }

}

?>