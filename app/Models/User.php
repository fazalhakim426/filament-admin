<?php

namespace App\Models;

use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use HasFactory, Notifiable;
    protected $guard_name = ['web', 'api'];

    use HasSuperAdmin;
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'balance',
    ];
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    function products(): HasMany
    {
        return $this->hasMany(Product::class, 'supplier_user_id', 'id');
    }

    function supplier_orders(): HasMany
    {
        return $this->hasMany(Order::class, 'supplier_user_id', 'id');
    }
    function orders(): HasMany
    {
        return $this->belongsToMany(Order::class);
    }
    
    function city(){
        return $this->belongsTo(City::class);
    }

}
