<?php

namespace App\Filament\Resources\Deposit\AllDepositResource\Pages;

use App\Filament\Resources\Deposit\AllDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllDeposit extends EditRecord
{
    protected static string $resource = AllDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
