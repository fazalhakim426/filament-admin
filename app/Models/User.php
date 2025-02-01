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
use App\Models\Deposit;
use App\Models\State;
use App\Models\City;
use App\Models\Country;

use Illuminate\Support\Str;

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
        return $this->hasOne(SupplierDetail::class, 'user_id', 'id');
    }

    function orderAsCustomer()
    {
        return $this->hasMany(Order::class, 'customer_user_id');
    }
    function supplierOrderItems()
    {
        return $this->hasMany(OrderItem::class, 'supplier_user_id', 'id');
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
    function state()
    {
        return $this->belongsTo(State::class);
    }
    function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }

    public function depositAsReferrals(): HasMany
    {
        return $this->hasMany(Deposit::class, 'referral_id');
    }
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'reseller_user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->password = Hash::make('password');
            $user->referral_code = Str::upper(Str::random(8));
        });
    }
}
