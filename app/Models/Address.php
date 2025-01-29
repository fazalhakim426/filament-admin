<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;
    function country() : BelongsTo  {
        return $this->belongsTo(Country::class, 'country_id');
    }
    function state() : BelongsTo  {
        return $this->belongsTo(State::class, 'state_id');
    }
    function city() : BelongsTo  {
        return $this->belongsTo(City::class, 'city_id');
    }
    function user() : BelongsTo  {
        return $this->belongsTo(User::class, 'user_id');
    }
}
