<?php

namespace App\Models;

use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens;
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
 
    function supplierDetail()
    {
        return $this->hasOne(SupplierDetail::class, 'user_id','id');
    }

    function orders()
    {
        return $this->belongsToMany(Order::class);
    }
    function supplierOrderItems()
    {
        return $this->hasMany(OrderItem::class,'supplier_user_id','id');
    }
    //supplier order
    public function ordersAsSupplier()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'supplier_user_id', 'id', 'id', 'order_id')
                    ->distinct(); // Using distinct to ensure unique orders are returned
    } 

    function city()
    {
        return $this->belongsTo(City::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->password = Hash::make('password');
        });
    }
}
