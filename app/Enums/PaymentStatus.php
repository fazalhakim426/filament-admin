<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    // [ 'unpaid', 'paid', 'refunded']
    case Unpaid = 'unpaid'; 
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid', 
            self::Paid => 'Paid',
            self::Refunded => 'Refunded', 
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::Refunded => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Unpaid =>  'heroicon-m-arrow-path', 
            self::Paid => 'heroicon-m-check-badge',
            self::Refunded => 'heroicon-m-x-circle',
        };
    }
}
