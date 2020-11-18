<?php

namespace App\Http\Controllers\API;

use App\FavouriteProducts;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\SanitizeTrait;

class FavouriteController extends Controller {
    
    use SanitizeTrait;

    public function index(Request $request){
        $user_id = $request->user()->id;
        $product = new Product();
        
        $products = FavouriteProducts::where([ ['user_id', $user_id] ])
        ->select('products.*' ,'parent_categories.id as parent_category_id', 'parent_categories.name as parent_category_name')
        ->join('products','products.id','favourite_products.product_id')
        ->join('category_products','category_products.product_id','products.id')
        ->join('parent_categories','category_products.parent_category_id','parent_categories.id')
        ->withCasts(
            $product->casts
        )->orderBy('favourite_products.created_at','DESC')->get();

        return response()->json(['data' => $products ]);
    }

    public function update($product_id, Request $request){
        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.favourite' => 'required',
        ]);

        $favourite = strtolower( $this->sanitizeField($validated_data['data']['favourite']) );

        if ($favourite == 'true') {
            if( !FavouriteProducts::where([ ['user_id', $user_id], ['product_id', $product_id] ])->exists()) {
                $favourite = new FavouriteProducts();
                $favourite->product_id = $product_id;
                $favourite->user_id = $user_id;
                $favourite->save();
            }
        } else {
            FavouriteProducts::where([ ['user_id', $user_id], ['product_id', $product_id] ])->delete();
        }

        return response()->json(['data' => ['status' => 'success']]);

    }
    
}
