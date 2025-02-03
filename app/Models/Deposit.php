<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Deposit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_reference',
        'user_id',
        'order_id',
        'referral_id',
        'amount',
        'transaction_type',
        'deposit_type',
        'currency',
        'provider',
        'balance',
        'description',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    } 

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
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
}
