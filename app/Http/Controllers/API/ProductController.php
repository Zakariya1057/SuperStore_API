<?php

namespace App\Http\Controllers\API;

use App\Product;
use App\Recommended;
use App\FavouriteProducts;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MonitoredProduct;
use Exception;
use App\Traits\SanitizeTrait;

class ProductController extends Controller {

    use SanitizeTrait;

    public function show(Request $request, $product_id){

        $product = new Product();
        $casts = $product->casts;

        $product_id = $this->sanitizeField($product_id);

        $product = Product::where('products.id',$product_id)
        ->select(
            'products.*',
            'parent_categories.id as parent_category_id',
            'parent_categories.name as parent_category_name',
            'promotions.id as promotion_id',
            'promotions.name as promotion'
        )
        ->join('category_products','category_products.product_id','products.id')
        ->leftJoin('promotions', 'promotions.id','=','products.promotion_id')
        ->join('parent_categories','category_products.parent_category_id','parent_categories.id')
        ->withCasts($casts)
        ->get()
        ->first();

        if(!$product){
            throw new Exception('No product found.', 404);
        }

        $user_id = $request->user()->id;
        
        // $product_details = Cache::remember('product_'.$product->id, 86400, function () use($product) {

            $product->ingredients;

            if (count($product->reviews) > 0){
                $product->reviews[0]->name = $product->reviews[0]->user->name;
            }
           
            $recommended = Recommended::where([ ['recommended.product_id',$product->id] ])
            ->join('products','products.id','recommended_product_id')
            ->withCasts(
                $product->casts
            )->get();
    
            $product->recommended = $recommended;
           
            $product->favourite = FavouriteProducts::where([ ['user_id', $user_id], ['product_id', $product->id] ])->exists();
            $product->monitoring = MonitoredProduct::where([ ['user_id', $user_id], ['product_id', $product->id] ])->exists();
            
            // return $product;
        // });

        return response()->json(['data' => $product]);
    }

}