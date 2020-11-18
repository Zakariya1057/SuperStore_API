<?php

namespace App;
use App\Casts\HTMLDecode;

use Illuminate\Database\Eloquent\Model;

class GroceryList extends Model
{
    public $casts = [
        'name' => HTMLDecode::class,
        'total_price' => 'double',
        'old_total_price' => 'double',
        'created_at' => 'datetime:d F Y',
    ];
}
