<?php

namespace App\Http\Controllers\API;

use App\Promotion;
use App\Http\Controllers\Controller;
use App\Traits\SanitizeTrait;
class PromotionController extends Controller {

    use SanitizeTrait;

    public function index($promotion_id){
        $promotion_id = $this->sanitizeField($promotion_id);
        $promotion = Promotion::where('id', $promotion_id)->get()->first();
        $promotion->products;
        return response()->json(['data' => $promotion]);
    }
}
