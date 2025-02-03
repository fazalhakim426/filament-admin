<?php

namespace App\Filament\Resources\Deposit\ReferralDepositResource\Pages;

use App\Filament\Resources\Deposit\ReferralDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReferralDeposit extends EditRecord
{
    protected static string $resource = ReferralDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
