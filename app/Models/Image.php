<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['url', 'imageable_id', 'imageable_type'];

    /**
     * Get the parent model (morphable) that owns the image.
     */
    public function imageable()
    {
        return $this->morphTo();
    }
    
}
