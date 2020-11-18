<?php

namespace App\Traits;

use App\Casts\HTMLDecode;
use App\GroceryList;
use App\GroceryListItem;
use App\Product;
use App\Casts\PromotionCalculator;
use App\CategoryProduct;
use App\ChildCategory;
use App\FeaturedItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait GroceryListTrait {

    protected function update_list($list){
        
        if($list instanceOf GroceryList){

            $product = new Product();
            $casts = $product->casts;

            $items =  GroceryListItem::
            join('products','products.id','grocery_list_items.product_id')
            ->leftJoin('promotions','promotions.id','products.promotion_id')
            ->where('list_id',$list->id)
            ->select(
                'products.id as product_id',
                'grocery_list_items.quantity as product_quantity',
                'products.price as product_price',
                'grocery_list_items.total_price',
                'grocery_list_items.ticked_off',
                'promotions.id as promotion_id',
                'promotions.name as promotion'
            )
            ->withCasts($casts)
            ->get();

            $status = 'Not Started';

            $total_price = 0;

            $total_items = count($items);
            $ticked_off_items = 0;

            $promotions = [];

            foreach($items as $item){

                if(!is_null($item->promotion)){
                    $promotion = (object)$item->promotion;

                    if(key_exists($promotion->id,$promotions)){
                        $promotions[$promotion->id]['products'][] = $item;
                    } else {
                        $promotions[$promotion->id] = [
                            'details' => $promotion,
                            'products' => [$item],
                         ];
                    }
                }

                $total_price += $item->total_price;

                if($item->ticked_off == 1){
                    $ticked_off_items++;
                }
            }

            $new_total_price = 0;

            $update = ['old_total_price' => NULL, 'total_price' => $total_price, 'status' => $status];

            foreach($promotions as $promotion){
                $promotion = (object)$promotion;

                $promotion_details = $promotion->details;
                $products = $promotion->products;

                $product_count = count($products);

                $quantity = $promotion_details->quantity;

                $new_total = 0;

                if($product_count >= $quantity){

                    $highest_price = 0;
                    $previous_total_price = 0;
                    $total_quantity = 0;

                    // Get the most expensive item
                    foreach($products as $product){
                        $previous_total_price = $previous_total_price + $product->total_price;
                        $total_quantity = $total_quantity + $product->product_quantity;

                        if($product->product_price > $highest_price){
                            $highest_price = $product->product_price;
                        }
                    }
                    
                    $remainder = ($total_quantity % $promotion_details->quantity);
                    $goes_into_fully = floor($total_quantity / $promotion_details->quantity);

                    if( !is_null($promotion_details->for_quantity)){
                        $new_total = ( $goes_into_fully * ( $promotion_details->for_quantity * $highest_price)) + ($remainder * $highest_price);
                    } else {
                        Log::debug("($goes_into_fully * $promotion_details->price) + ($remainder * $highest_price);");
                        $new_total = ($goes_into_fully * $promotion_details->price) + ($remainder * $highest_price);
                    }

                    $new_total_price = ($total_price - $previous_total_price) + $new_total;

                    if($new_total != $previous_total_price){
                        $update['old_total_price'] = $total_price;
                        $update['total_price'] = $new_total_price;
                    }

                }

            }

            if($ticked_off_items == $total_items){
                $update['status'] = 'Completed';
            } elseif($ticked_off_items > 0){
                $update['status'] = 'In Progress';
            }

            DB::transaction(function () use($list, $update){
                GroceryList::where('id',$list->id)->update($update);
            }, 5);
            
        }

    }

    protected function show_list($list_id, $user_id){

        $product = new Product();

        $list = GroceryList::where([ [ 'id', $list_id],['user_id', $user_id] ])->first();

        if(!$list){
            throw new Exception('No list found for user.', 404);
        }

        $product = new Product();

        $casts = $product->casts ?? [];
        $casts['promotion'] = PromotionCalculator::class;

        $list_items = GroceryListItem::where([ ['list_id', $list->id] ])
        ->select([
            'grocery_list_items.id as id',
            'grocery_list_items.product_id as product_id',
            'parent_categories.name as category_name',
            'parent_categories.id as category_id',
            'products.name as name',
            'grocery_list_items.total_price as total_price',
            'products.price as price',
            'grocery_list_items.quantity as quantity',
            'category_aisles.name as aisle_name',
            'products.weight as weight',
            'products.small_image as small_image',
            'products.large_image as large_image',
            'grocery_list_items.ticked_off as ticked_off',
            'promotions.id as promotion_id',
            'promotions.name as promotion'
        ])
        ->join('parent_categories', 'parent_categories.id','=','grocery_list_items.parent_category_id')
        ->join('products', 'products.id','=','grocery_list_items.product_id')
        ->leftJoin('promotions', 'promotions.id','=','products.promotion_id')
        ->leftJoin('category_aisles', function ($join) use($list) {
            $join->on('category_aisles.category_id','=','grocery_list_items.parent_category_id')
                 ->where('category_aisles.store_id',$list->store_id);
        })
        ->orderBy('grocery_list_items.parent_category_id','ASC')
        ->withCasts(
            $casts
        )
        ->get();

        $categories = [];

        foreach($list_items as $list_item){

            $category_id = $list_item->category_id;
            $category_name = html_entity_decode($list_item->category_name, ENT_QUOTES);
            $aisle_name = $list_item->aisle_name;
            
            unset($list_item->category_name);
            unset($list_item->aisle_name);
            unset($list_item->category_id);
            
            if(key_exists($category_name, $categories)){
                $categories[$category_name]['items'][] = $list_item;
            } else {
                $categories[$category_name] = [ 
                    'id' => $category_id,
                    'name' =>  $category_name,
                    'aisle_name' => $aisle_name,
                    'items' => [$list_item]
                ];
            }
            
        }

        $list->categories = array_values($categories);

        return $list;
    }

    public function grocery_items($user_id){
        $product = new Product();
        return GroceryList::where('user_id', $user_id)
        ->select('products.*' ,'parent_categories.id as parent_category_id', 'parent_categories.name as parent_category_name')
        ->join('grocery_list_items','grocery_list_items.list_id','grocery_lists.id')
        ->join('products', 'products.id','=','grocery_list_items.product_id')
        ->orderBy('grocery_lists.updated_at', 'DESC')
        ->join('category_products','category_products.product_id','products.id')
        ->join('parent_categories','category_products.parent_category_id','parent_categories.id')
        ->limit(15)->groupBy('category_products.product_id')->withCasts($product->casts)->get();
    }

    protected function item_price($product_id,$quantity=1){
        $product = Product::where('products.id',$product_id)->leftJoin('promotions', 'promotions.id','=','products.promotion_id')->select('products.price', 'promotions.id as promotion_id','promotions.name as promotion')->withCasts(['promotion' => PromotionCalculator::class])->get()->first();
    
        $price = $product->price;
        $total = 0;

        if($quantity == 0){
            return $total;
        }

        if(!is_null($product->promotion)){
            $promotion_details = (object)$product->promotion;
            $remainder = ($quantity % $promotion_details->quantity);
            $goes_into_fully = floor($quantity / $promotion_details->quantity);

            if($quantity < $promotion_details->quantity){
                $total = $quantity * $price;   
            } else {

                if( !is_null($promotion_details->for_quantity)){
                    $total = ( $goes_into_fully * ( $promotion_details->for_quantity * $price)) + ($remainder * $price);
                } else {
                    $total = ($goes_into_fully * $promotion_details->price) + ($remainder * $price);
                }
            }

        } else {
            $total = $quantity * $price;
        }
        
        return $total;

    }

    protected function update_list_items($list_id, $items){
        // Delete all list items, create new ones
        GroceryListItem::where('list_id', $list_id)->delete();

        foreach($items as $item){

            $quantity = $item['quantity'] ?? 1;
            $ticked_off = strtolower($item['ticked_off'] ?? 'false') == 'true' ? 1 : 0;
            $product_id = $item['product_id'];
            $total_price = $this->item_price($product_id, $quantity);

            $parent_category_id = CategoryProduct::where('product_id', $product_id)->select('parent_category_id')->first()->parent_category_id;
            
            GroceryListItem::create(
                [
                    'list_id' => $list_id, 
                    'product_id' =>  $product_id,
                    'parent_category_id' => $parent_category_id, 
                    'quantity' => $quantity,
                    'ticked_off' =>  $ticked_off,
                    'total_price' => $total_price
                ]
            );

        }

    }

}

?>