<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Traits\GroceryTrait;
use App\Traits\SanitizeTrait;

class GroceryController extends Controller {
    
    use GroceryTrait;
    use SanitizeTrait;

    public function categories($store_type_id){

        $store_type_id = $this->sanitizeField($store_type_id);

        $grand_parent_categories = Cache::remember('categories_'.$store_type_id, now()->addWeek(1), function () use ($store_type_id){
            return $this->grocery_categories($store_type_id);
        });

        return response()->json(['data' => $grand_parent_categories]);
    }

    public function products($parent_cateogy_id){

        $parent_cateogy_id = $this->sanitizeField($parent_cateogy_id);

        $categories = Cache::remember('category_products_'.$parent_cateogy_id, now()->addWeek(1), function () use ($parent_cateogy_id){
            return $this->grocery_products($parent_cateogy_id);
        });

        return response()->json(['data' => $categories]);
    }
}
