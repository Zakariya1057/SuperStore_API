<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PromotionCalculator implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if(is_null($value)){
            return;
        }

        $name = null;

        $value = html_entity_decode($value, ENT_QUOTES);
        preg_match('/(\d+).+£(\d+\.*\d*)$/',$value,$price_promotion_matches);

        $quantity = $price = $for_quantity = null;

        if($price_promotion_matches){
            $quantity = (int)$price_promotion_matches[1];
            $price = (float)$price_promotion_matches[2];
        }

        preg_match('/(\d+).+\s(\d+)$/',$value,$quantity_promotion_matches);
        if($quantity_promotion_matches){
            $quantity = (int)$quantity_promotion_matches[1];
            $for_quantity = (int)$quantity_promotion_matches[2];
        }

        if(!$quantity_promotion_matches && !$price_promotion_matches){
            return null;
        }

        return ['id' => $model->promotion_id,'name' => $value, 'quantity' => $quantity, 'price' => $price, 'for_quantity' => $for_quantity];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
