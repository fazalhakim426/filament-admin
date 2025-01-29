<?php

namespace App\Filament\Resources\DepositResource\Pages;

use App\Filament\Resources\DepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageDeposits extends ManageRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'Credit' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('transaction_type', 'credit')),
            'Debit' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('transaction_type', 'debit')),
        ];
    }
}
