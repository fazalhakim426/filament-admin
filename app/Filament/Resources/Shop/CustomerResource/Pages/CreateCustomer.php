<?php

namespace App\Filament\Resources\Shop\CustomerResource\Pages;

use App\Filament\Resources\Shop\CustomerResource;
use Filament\Resources\Pages\CreateRecord; 
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $supplierCustomer = $this->record;
        $supplierCustomer->update([
            'referral_code' => Str::upper(Str::random(8))
        ]);
        $supplierCustomer->assignRole('customer');
    }
}
