<?php

namespace App\Filament\Resources\Deposit\OrderDepositResource\Pages;

use App\Filament\Resources\Deposit\OrderDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderDeposit extends EditRecord
{
    protected static string $resource = OrderDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
