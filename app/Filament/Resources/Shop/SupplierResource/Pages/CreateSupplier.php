<?php

namespace App\Filament\Resources\Shop\SupplierResource\Pages;

use App\Events\UserCreated;
use App\Filament\Resources\Shop\SupplierResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
     
    
    protected function afterCreate(): void
    { 
        $supplierUser = $this->record;
        $supplierUser->assignRole('supplier');
        $password= Str::random(5); 
        $supplierUser->password = Hash::make($password);
        event(new UserCreated($supplierUser,$password));
        
    }

}   
