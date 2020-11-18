<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;

class Review extends Model
{

    protected $casts = [
        'text' => HTMLDecode::class,
        'title' => HTMLDecode::class,  
        'updated_at' => 'datetime:d F Y',
        'created_at' => 'datetime:d F Y',
    ];

    public function user() {
        return $this->belongsTo('App\User')->select('name');
    }
}