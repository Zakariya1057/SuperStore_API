<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\GroceryListTrait;
use App\Traits\GroceryTrait;
use App\Traits\MonitoringTrait;
use App\Traits\PromotionTrait;
use App\Traits\StoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller {
    use StoreTrait;
    use MonitoringTrait;
    use PromotionTrait;
    use GroceryListTrait;
    use GroceryTrait;

    public function show(Request $request){
        $user = $request->user();

        $monitoring = $this->monitoring_products($user->id);
        $lists = $this->lists_progress($user->id);
        $groceries = $this->grocery_items($user->id);

        // Cache::flush();

        $data = Cache::remember('home_page', now()->addWeek(1), function (){
            $featured_items = $this->featured_items();
            $stores = $this->stores_by_type(1,false);
            $categories = $this->home_categories();
            $promotions = $this->store_promotions(1);

            return [
                'stores' => $stores,
                'featured' => $featured_items,
                'promotions' => $promotions,
                'categories' => $categories,
            ];

        });

        $data['monitoring'] = $monitoring;
        $data['lists'] = $lists;
        $data['groceries'] = $groceries;

        foreach($data as $key => $value){
            if($value == []){
                $data[$key] = null;
            }
        }

        return response()->json(['data' => $data]);
    }

}
