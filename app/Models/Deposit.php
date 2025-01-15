<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'order_id',
        'order_supplier_user_id',
        'amount',
        'transaction_type',
        'deposit_type',
        'transaction_reference'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($deposit) {
            $deposit->adjustBalance($deposit->amount);
        });
        static::deleted(function ($deposit) {
            $deposit->adjustBalance(-$deposit->amount);
        });
    }

    protected function adjustBalance($adjustment)
    {
        $this->update(['balance' => $this->user->balance + $adjustment]);
        $this->user->update(['balance' => $this->user->balance + $adjustment]);
    }
    function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
     
    function referrel() {
        return $this->belongsTo(Referral::class, 'referrel_id', 'id');
    }
    
}