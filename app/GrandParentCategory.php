<?php

namespace App;

use App\Casts\HTMLDecode;
use Illuminate\Database\Eloquent\Model;

class GrandParentCategory extends Model
{
    public $visible = ['id', 'name','child_categories'];

    protected $casts = [
        'name' => HTMLDecode::class
    ];

    public function child_categories() {
        return $this->hasMany('App\ParentCategory','parent_category_id');
    }
}
