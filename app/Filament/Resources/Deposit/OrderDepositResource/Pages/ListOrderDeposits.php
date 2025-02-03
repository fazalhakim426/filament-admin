<?php

namespace App\Filament\Resources\Deposit\OrderDepositResource\Pages;

use App\Filament\Resources\Deposit\OrderDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderDeposits extends ListRecords
{
    protected static string $resource = OrderDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
