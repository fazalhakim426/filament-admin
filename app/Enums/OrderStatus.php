<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Processing = 'processing';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';

    case ReadyToDispatch = "ready-to-dispatch";
    case Dispatched = "dispatched";
    case InTransit = "in-transit";
    case ReturnOrder = "return-orders";
    case Completed = "completed";

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Processing => 'Processing',
            self::Confirmed => 'Confirmed',
            self::Canceled => 'Canceled',
            self::ReadyToDispatch => 'Ready to Dispatch',
            self::Dispatched => 'Dispatched',
            self::InTransit => 'In Transit',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::ReturnOrder => 'Return Orders',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::New => 'info',
            self::Processing => 'warning',
            self::Confirmed => 'success',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Canceled => 'danger',
            self::ReadyToDispatch => 'warning',
            self::Dispatched => 'primary',
            self::InTransit => 'gray',
            self::ReturnOrder => 'danger',
            self::Completed => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::New => 'heroicon-m-sparkles',
            self::Processing => 'heroicon-m-arrow-path',
            self::Confirmed => 'heroicon-m-check-badge',
            self::Shipped => 'heroicon-m-truck',
            self::Delivered => 'heroicon-m-home-modern',
            self::Canceled => 'heroicon-m-x-circle',
            self::ReadyToDispatch => 'heroicon-m-clock',
            self::Dispatched => 'heroicon-m-arrow-up-right',
            self::InTransit => 'heroicon-m-map',
            self::ReturnOrder => 'heroicon-m-arrow-uturn-left',
            self::Completed => 'heroicon-m-check-circle',
        };
    }
}
