<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Filament\Resources\Shop\OrderResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
        
            'new' => Tab::make()->query(fn ($query) => $query->where('order_status', 'new')->orWhere('order_status', 'processing')),
            'confirmed' => Tab::make()->query(fn ($query) => $query->where('order_status', 'confirmed')),
            'shipped' => Tab::make()->query(fn ($query) => $query->where('order_status', 'shipped')->orWhere('order_status', 'delivered')),
            'canceled' => Tab::make()->query(fn ($query) => $query->where('order_status', 'canceled')),
            'dispatch' => Tab::make()->query(fn ($query) => $query->where('order_status', 'ready-to-dispatch')->orWhere('order_status', 'dispatched')),
            'transit' => Tab::make()->query(fn ($query) => $query->where('order_status', 'in-transit')),
            'return' => Tab::make()->query(fn ($query) => $query->where('order_status', 'return-orders')),
            'completed' => Tab::make()->query(fn ($query) => $query->where('order_status', 'completed')),
        
            // This one below you need to check — 'refunded' does not exist in your enum currently
            'refunded' => Tab::make()->query(fn ($query) => $query->where('order_status', 'refunded')),
        ];
        
    }
}
