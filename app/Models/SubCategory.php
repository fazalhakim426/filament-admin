<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    
    use HasFactory; 
    protected $fillable = ['name','description'];
    public $timestamps  = false;
    
    function products() {
        return $this->hasMany(Product::class);
    }
    function category() {
        return $this->belongsTo(Category::class);
    }
}

