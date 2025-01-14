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
        'amount',
        'transaction_type',
        'deposit_type',
        'transaction_reference'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($deposit) {
            $deposit->adjustUserBalance($deposit->amount);
        });

        static::deleted(function ($deposit) {
            $deposit->adjustUserBalance(-$deposit->amount);
        });
        // static::restored(function ($deposit) {
        //     $deposit->adjustUserBalance($deposit->amount);
        // });
    }

    protected function adjustUserBalance($adjustment)
    {
        $this->update(['balance' => $this->user->balance + $adjustment,]);
        $this->user->update(['balance' => $this->user->balance + $adjustment]);
    }
}
