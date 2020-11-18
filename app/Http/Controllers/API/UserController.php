<?php

namespace App\Http\Controllers\API;

use App\FavouriteProducts;
use App\GroceryList;
use App\GroceryListItem;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\MonitoredProduct;
use App\Review;
use App\StoreType;
use App\Traits\GroceryListTrait;
use App\Traits\SanitizeTrait;
use App\Traits\UserTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class UserController extends Controller {

    use UserTrait;
    use SanitizeTrait;
    use GroceryListTrait;

    public function register(Request $request){

        $validated_data = $request->validate([
            'data.name' => 'required|string|max:255',
            'data.email' => 'required|email|max:255',
            'data.password' => 'required|confirmed|string|min:8|max:255',
            'data.identifier' => '',
            'data.user_token' => '',
            'data.notification_token' => 'required|string|max:255'
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $identifier = $data['identifier'] ?? '';
        $user_token = $data['user_token'] ?? '';
        $notification_token = $data['notification_token'];
        
        $user_data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'notification_token' => $data['notification_token']
        ];

        $user = User::where('email', $data['email'])->get()->first();
        $userExists = !is_null($user);

        if( $identifier != "" && $user_token != ""){
            // Apple Login

            if(!$this->validate_apple_login($data)){
                throw new Exception('Invalid user data provided.', 422);
            } else {
                $apple_user = User::where('identifier', $identifier)->get()->first();

                // User Found In Database. Or user exists with that email.
                if( !is_null($apple_user) || $userExists){
                    $token_data = $this->create_token($apple_user ?? $user, $notification_token);
                    return response()->json(['data' => $token_data]);
                } else {
                    $user_data['identifier'] = $identifier;
                }

            }

        }

        if($userExists){
            throw new Exception('Email address belongs to another user.', 422);
        }

        
        $user = User::create($user_data);

        try {
            // Create default starting list for new users.

            $total_price = 0;

            $uuid = Uuid::uuid4();
            $identifier = $uuid->toString();
    
            $list = new GroceryList();
            $list->name = 'Shopping List';
            $list->user_id = $user->id;
            $list->identifier = $identifier;
            $list->save();
    
            $products = [1,2,3,4];
    
            foreach($products as $product_id){
                $item = new GroceryListItem();
                $item->list_id = $list->id;
                $item->product_id = $product_id;
                $item->parent_category_id = 1;
                $item->quantity = 1;
                $item->ticked_off = false;

                $price =  $this->item_price($product_id);
                $total_price += $price;
                $item->total_price = $price;

                $item->save();
            }

            $list->total_price = $total_price;
            $list->save();
        } catch(Exception $e) {
            Log::error('Failed To Create Starting List: '.$e->getMessage());
        }
       
        $token_data = $this->create_token($user, $notification_token);
        return response()->json(['data' => $token_data]);

    }
    

    public function login(Request $request){

        $validated_data = $request->validate([
            'data.email' => 'required|email|max:255',
            'data.password' => 'required|string|min:8|max:255',
            'data.notification_token' => ''
        ]);
        
        $data = $this->sanitizeAllFields($validated_data['data']);
        
        $notification_token = $data['notification_token'];

        $user = User::where('email', $data['email'])->get()->first();

        if(!$user){
            throw new Exception('Email address doesn\'t belongs to any user.', 404);
        }

        if (Hash::check($data['password'], $user->password)) {
            $user->tokens()->delete();
            $token_data = $this->create_token($user, $notification_token);
            return response()->json(['data' => $token_data]);
        } else {

            if(!is_null($user->identifier)){
                throw new Exception('Your account is connected to Apple. Use the Apple button to log in.', 422);
            } else {
                throw new Exception('Incorrect password.', 404);
            }
            
        }

    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        User::where('id', $request->user()->id)->update(['logged_out_at' => Carbon::now(), 'notification_token' => NULL]);
        return response()->json(['data' => ['status' => 'success']]);
    }

    public function update(Request $request){

        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.email' => [],
            'data.name' => [],
            'data.password' => [],
            'data.password_confirmation' => [],
            'data.current_password' => [],
            'data.type' => [],
            'data.send_notifications' => [],
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        if(!key_exists('type',$data)){
            throw new Exception('Field Type required.', 422);
        }
        

        $type = $data['type'];
        $value = $data[ $data['type'] ];

        if($type == 'password'){
            $type = 'edit_password';
        }

        if($type == 'send_notifications'){
            $data['send_notifications'] = (bool)$data['send_notifications'] ?? null;
        }

        $error = $this->validate_field($data,$type,$request->user()->id);
        if($error){
            return $error;
        }

        if($type == 'edit_password'){
           $value  = Hash::make($value);
        }

        User::where('id',$user_id)->update([$data['type'] => $value ]);

        return response()->json(['data' => ['status' => 'success']]);

    }

    public function delete(Request $request){
        $user = $request->user();

        if(StoreType::where('user_id', $user->id)->exists()){
            throw new Exception('Failed to delete store account.', 402);
        }

        $reviews = Review::where('user_id', $user->id)->join('products', 'products.id', 'reviews.product_id')->groupBy('products.store_type_id')->get();

        foreach($reviews as $review){
            Review::where('user_id', $user->id)->update(['user_id' => $review->store_type_id]);
        }

        $lists = GroceryList::where('user_id', $user->id)->get();

        foreach($lists as $list){
            GroceryListItem::where('list_id', $list->id)->delete();
            $list->delete();
        }
        
        FavouriteProducts::where('user_id', $user->id)->delete();
        MonitoredProduct::where('user_id', $user->id)->delete();

        User::where('id', $user->id)->delete();

        $request->user()->tokens()->delete();

        return response()->json(['data' => ['status' => 'success']]);
    }

    private function create_token($user, $notification_token = null){
        $token = $user->createToken($user->id)->plainTextToken;

        $update_fields = ['logged_in_at' => Carbon::now(), 'notification_token' => $notification_token];

        if(is_null($notification_token)){
            $update_fields['send_notifications'] = 0;
            $send_notifications = false;
        } else {
            $update_fields['send_notifications'] = 1;
            $send_notifications = true;
        }

        User::where('id', $user->id)->update($update_fields);
        return ['id' => $user->id, 'token' => $token, 'name' => $user->name, 'email' => $user->email,'send_notifications' => $send_notifications];
    }

}
