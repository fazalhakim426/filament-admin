<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

use Filament\Resources\Components\Tab;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'Manzil Choice' => Tab::make()->query(fn($query) => $query->where('manzil_choice', true)),
            'Active' => Tab::make()->query(fn($query) => $query->where('is_active', true)),
        ];
    }
}
