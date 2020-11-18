<?php

namespace App\Http\Controllers\API;

use App\CategoryProduct;
use App\GroceryList;
use App\GroceryListItem;
use App\Traits\GroceryListTrait;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Traits\SanitizeTrait;

class ListViewController extends Controller {

    use GroceryListTrait;
    use SanitizeTrait;

    public function index(Request $request){
        //Use user_id to get all lists for user

        $user_id = $request->user()->id;
        $lists = GroceryList::where('user_id', $user_id)->orderBy('created_at', 'DESC')->get();
        return response()->json(['data' => $lists]);
    }

    public function create(Request $request){

        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.name' => 'required|max:255',
            'data.identifier' => 'required',
            'data.store_type_id' => 'required',
            'data.items' => ''
        ]);
        
        $data = $this->sanitizeAllFields($validated_data['data']);

        $items = $data['items'] ?? [];

        $list_name = $data['name'];
        $store_type_id = $data['store_type_id'];
        $identifier = $data['identifier'];

        if( GroceryList::where('identifier',$identifier)->exists() ){
            throw new Exception('List with identifier found in database.', 409);
        }

        $list = new GroceryList();
        $list->name = $list_name;
        $list->store_type_id = $store_type_id;
        $list->user_id = $user_id;
        $list->identifier = $identifier;
        $list->save();

        $this->update_list_items($list->id, $items);
        $this->update_list($list);
    
        return $this->index($request);

    }

    public function show(Request $request, $list_id){
        $user_id = $request->user()->id;

        $list_id = $this->sanitizeField($list_id);
        $list = $this->show_list($list_id, $user_id);

        if($list instanceOf Request){
            return $list;
        } else {
            return response()->json(['data' => $list]);
        }
        
    }

    public function delete(Request $request){
        // Delete shopping list and all shopping items within

        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.identifier' => 'required',
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $list = GroceryList::where([['identifier',$data['identifier']],['user_id', $user_id]])->get()->first();

        GroceryListItem::where('list_id',$list->id)->delete();

        GroceryList::where([ ['id',$list->id], ['user_id', $user_id] ])->delete();

        return response()->json(['data' => ['status' => 'success']]);
    }

    public function update(Request $request){
        // Item ticked off, or quantity changed

        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.identifier' => 'required',
            'data.store_type_id' => 'required',
            'data.name' => 'required',
            'data.items' => ''
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $items = $data['items'] ?? [];

        $name = $data['name'];
        $store_type_id = $data['store_type_id'];

        $list = GroceryList::where([['identifier',$data['identifier']],['user_id', $user_id]])->get()->first();

        if(is_null($list)){
            throw new Exception('No list found.', 404);
        } else {
            GroceryList::where([['identifier',$data['identifier']],['user_id', $user_id]])
            ->update([
                'name' => $name,
                'store_type_id' => $store_type_id
            ]);

            $list_id = $list->id;

            $this->update_list_items($list_id, $items);
            $this->update_list($list);

        }

        // If all products ticked off, then change status to complete
        return response()->json(['data' => ['status' => 'success']]);
    }

    public function restart(Request $request, $list_id){

        $user_id = $request->user()->id;

        $list_id = $this->sanitizeField($list_id);

        //Make sure that list belongs to user
        $list = GroceryList::where([ ['id',$list_id], ['user_id', $user_id] ])->get()->first();

        if($list){

            GroceryListItem::where([['list_id', $list->id]])
            ->update([
                'ticked_off' => 0
            ]);

        }

        return response()->json(['data' => ['status' => 'success']]);
        
    }

}
