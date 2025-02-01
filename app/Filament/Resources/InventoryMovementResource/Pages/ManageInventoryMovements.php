<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
class ManageInventoryMovements extends ManageRecords
{
    protected static string $resource = InventoryMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'addition' => Tab::make('Addition')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'addition')),
            'deduction' => Tab::make('Deduction')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'deduction')),
        ];
    }
}
