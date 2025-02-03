<?php

namespace App\Filament\Resources\Deposit\AllDepositResource\Pages;

use App\Filament\Resources\Deposit\AllDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllDeposits extends ListRecords
{
    protected static string $resource = AllDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
