<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    //['new', 'processing','confirmed', 'shipped', 'delivered', 'canceled']
    case New = 'new'; 
    case Processing = 'processing';
    case Confirmed = 'confirmed'; 
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New', 
            self::Processing => 'Processing',
            self::Confirmed => 'confirmed', 
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Canceled => 'Canceled',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info', 
            self::Processing => 'warning',
            self::Shipped, self::Delivered, self::Confirmed  => 'success',
            self::Canceled  => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Processing => 'heroicon-m-arrow-path',
            // self::Pending => 'heroicon-m-arrow-path',
            self::Shipped => 'heroicon-m-truck',
            self::Delivered, self::Delivered, self::Confirmed  => 'heroicon-m-check-badge',
            self::Canceled => 'heroicon-m-x-circle',
        };
    }
}
