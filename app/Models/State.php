<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
   
    public $timestamps  = false;
    protected $fillable = ['name','code'];
    function country() {
        return $this->belongsTo(Country::class);
    }

    function cities() {
        return $this->hasMany(City::class);
    }

}
