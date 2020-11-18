<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;
use App\Casts\Image;

class StoreType extends Model
{
    public $casts = [
        'large_logo' => Image::class,
        'small_logo' => Image::class,
    ];
}
