<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Casts\HTMLDecode;

class Store extends Model
{

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'name' => HTMLDecode::class
    ];

    public function opening_hours() {
        return $this->hasMany('App\OpeningHour');
    }

    public function facilities(){
        return $this->hasMany('App\Facility');
    }
    
    public function location(){
        return $this->hasOne('App\StoreLocation');
    }
}
