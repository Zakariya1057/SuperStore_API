<?php

namespace App\Http\Controllers\API;

use App\CategoryProduct;
use App\Http\Controllers\Controller;
use App\GroceryList;
use App\GroceryListItem;
use App\Traits\GroceryListTrait;
use Exception;
use Illuminate\Http\Request;
use App\Traits\SanitizeTrait;

class GroceryListViewController extends Controller {

    use GroceryListTrait;
    use SanitizeTrait;

    public function create($list_id, Request $request){

        $validated_data = $request->validate([
            'data.product_id' => 'required',
            'data.quantity' => '',
            'data.ticked_off' => ''
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);
        $product_id = $data['product_id'];

        $parent_category_id = CategoryProduct::where('product_id', $product_id)->select('parent_category_id')->first()->parent_category_id;
       
        $quantity = $data['quantity'] ?? 1;
        $ticked_off = strtolower($data['ticked_off'] ?? 'false') == 'true' ? 1 : 0;

        $list = GroceryList::where('id', $list_id)->first();

        $total_price = $this->item_price($product_id, $quantity);

        if($list){
            GroceryListItem::updateOrCreate(
                [
                    'list_id' => $list_id, 
                    'product_id' =>  $product_id
                ],
    
                [
                    'parent_category_id' => $parent_category_id, 
                    'quantity' => $quantity,
                    'ticked_off' =>  $ticked_off,
                    'total_price' => $total_price
                ]
            );
        } else {
            throw new Exception('No list found.', 409);
        }

        $this->update_list($list);
        
        // Set off message queue to update list total.
        return response()->json(['data' => ['status' => 'success']]);

    }
    
    public function update($list_id, Request $request){
        // Item ticked off, or quantity changed
        $validated_data = $request->validate([
            'data.product_id' => 'required',
            'data.quantity' => 'required',
            'data.ticked_off' => 'required'
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $user_id = $request->user()->id;

        $list = GroceryList::where([ [ 'id',$list_id], ['user_id', $user_id] ])->first();

        if($list){

            $quantity = $data['quantity'];

            if($quantity == 0){
                GroceryListItem::where([['list_id',$list_id],['product_id', $data['product_id']]])->delete();
            } else {

                $total_price = $this->item_price($data['product_id'], $data['quantity']);
                $ticked_off = strtolower($data['ticked_off']) == 'true' ? 1 : 0;
    
                GroceryListItem::where([['list_id',$list_id],['product_id', $data['product_id']]])
                ->update([
                    'quantity' => $quantity,
                    'ticked_off' => $ticked_off,
                    'total_price' => $total_price
                ]);

            }

        }

        // If quantity change, update list total with job
        $this->update_list($list);

        // If all products ticked off, then change status to complete
        return response()->json(['data' => ['status' => 'success']]);
    }

    public function delete($list_id, Request $request){
        $validated_data = $request->validate([
            'data.product_id' => 'required',
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $product_id = $data['product_id'];

        $user_id = $request->user()->id;

        GroceryListItem::where([ ['list_id',$list_id], ['product_id',$product_id, ['user_id', $user_id]] ])->delete();

        $list = GroceryList::where([ [ 'id',$list_id], ['user_id', $user_id] ])->first();

        $this->update_list($list);

        return response()->json(['data' => ['status' => 'success']]);
    }

}
