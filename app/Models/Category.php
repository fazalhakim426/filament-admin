<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    
    use HasFactory; 
    protected $fillable = ['name','description'];
    public $timestamps  = false;

    function subCategory() {
        return $this->hasMany(SubCategory::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

}

