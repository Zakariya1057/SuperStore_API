<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroceryListItem extends Model
{
    protected $fillable = [
        'parent_category_id', 'quantity', 'ticked_off', 'total_price', 'list_id', 'product_id'
    ];

    public $casts = [
        'total_price' => 'double',
        'ticked_off' => 'Bool',
        'created_at' => 'datetime:d F Y',
    ];
}
