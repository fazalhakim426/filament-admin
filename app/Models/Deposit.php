<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Deposit extends Model
{
    use HasFactory, SoftDeletes;
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
            $deposit->transaction_reference = 'TRX-' . strtoupper(Str::random(10));
            $adjustment = $deposit->transaction_type == 'credit' ? $deposit->amount : -$deposit->amount;
            $deposit->update(['balance' => $deposit->user->balance + $adjustment]);
            $deposit->user->update(['balance' => $deposit->user->balance + $adjustment]);
        });
        static::deleted(function ($deposit) {
            $adjustment = $deposit->transaction_type == 'credit' ? $deposit->amount : -$deposit->amount;
            $adjustment = $adjustment * -1;
            $deposit->update(['balance' => $deposit->user->balance + $adjustment]);
            $deposit->user->update(['balance' => $deposit->user->balance + $adjustment]);
        });
    }
    function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    function referrel()
    {
        return $this->belongsTo(Referral::class, 'referrel_id', 'id');
    }
}
