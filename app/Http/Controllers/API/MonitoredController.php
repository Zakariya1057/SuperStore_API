<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MonitoredProduct;
use App\Traits\MonitoringTrait;
use App\Traits\SanitizeTrait;

class MonitoredController extends Controller {
    
    use MonitoringTrait;
    use SanitizeTrait;

    public function index(Request $request){
        $user_id = $request->user()->id;
        $products = $this->monitoring_products($user_id);
        return response()->json(['data' => $products ]);
    }

    public function update($product_id, Request $request){
        $user_id = $request->user()->id;

        $validated_data = $request->validate([
            'data.monitor' => 'required',
        ]);

        $data = $this->sanitizeAllFields($validated_data['data']);

        $monitor = strtolower($data['monitor']);

        if ($monitor == 'true') {
            if( !MonitoredProduct::where([ ['user_id', $user_id], ['product_id', $product_id] ])->exists()) {
                $favourite = new MonitoredProduct();
                $favourite->product_id = $product_id;
                $favourite->user_id = $user_id;
                $favourite->save();
            }
        } else {
            MonitoredProduct::where([ ['user_id', $user_id], ['product_id', $product_id] ])->delete();
        }

        return response()->json(['data' => ['status' => 'success']]);

    }
    
}
