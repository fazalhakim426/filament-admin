<?php

namespace App\Filament\Resources\Deposit\CashDepositResource\Pages;

use App\Filament\Resources\Deposit\CashDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashDeposit extends EditRecord
{
    protected static string $resource = CashDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
