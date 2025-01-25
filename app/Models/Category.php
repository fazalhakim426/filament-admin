<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    
    use HasFactory,SoftDeletes;
    public $timestamps    = false;
    protected $fillable = ['name','description'];
    
    function products() {
        return $this->hasMany(Product::class);
    }
}

