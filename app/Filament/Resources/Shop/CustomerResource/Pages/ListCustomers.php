<?php

namespace App\Filament\Resources\Shop\CustomerResource\Pages;

use App\Events\UserCreated;
use App\Filament\Resources\Shop\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use Filament\Resources\Components\Tab;
class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->after(function ($record) {
                $record->assignRole('customer'); 
                event(new UserCreated($record, Str::random(8)));  
            }),
        ];
    }
    public function getTabs(): array
    {
        return [
             null => Tab::make('All'),
             'active' => Tab::make()->query(fn ($query) => $query->where('active', true)),
             'inactive' => Tab::make()->query(fn ($query) => $query->where('active', false)),
            //  'pending request' => Tab::make()->query(fn ($query) => $query->where('new_supplier_request', true)),
        ];
    }
}
