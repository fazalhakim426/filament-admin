<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory; 
    protected $fillable = ['name'];
    public $timestamps  = false;
    function users() {
        return $this->hasMany(User::class);
    }
    function state() {
        return $this->belongsTo(State::class);
    } 
    //belong to country throug state
    function country() {
        return $this->state()->country();
    } 

}
