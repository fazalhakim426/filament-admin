<?php

namespace App\Filament\Resources\Deposit\ReferralDepositResource\Pages;

use App\Filament\Resources\Deposit\ReferralDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReferralDeposits extends ListRecords
{
    protected static string $resource = ReferralDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
