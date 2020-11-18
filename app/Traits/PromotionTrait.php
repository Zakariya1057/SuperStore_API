<?php

namespace App\Traits;

use App\FeaturedItem;
use App\Promotion;

trait PromotionTrait {

    protected function store_promotions($store_id){
        $promotion = new Promotion();
        return FeaturedItem::select('promotions.id as promotion_id', 'name as promotion')->whereRaw('type = "promotions" AND week = WEEK(NOW()) AND year = YEAR(NOW())')->join('promotions','promotions.id','featured_id')->withCasts($promotion->casts)->limit(10)->get()->pluck('promotion')->toArray();
    }

}

?>