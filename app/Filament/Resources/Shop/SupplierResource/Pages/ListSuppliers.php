<?php

namespace App\Filament\Resources\Shop\SupplierResource\Pages;

use App\Filament\Resources\Shop\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Events\UserCreated;
use Illuminate\Support\Str;
use Filament\Resources\Components\Tab;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function ($record) {
                    $record->assignRole('supplier');
                    event(new UserCreated($record, Str::random(8)));
                }),
        ];
    }
    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'active' => Tab::make()->query(fn($query) => $query->where('active', true)),
            'inactive' => Tab::make()->query(fn($query) => $query->where('active', false)),
            // 'suppliers' => Tab::make()->query(
            //     fn($query) => $query
            //         ->whereHas('roles', function ($query) {
            //             $query->where('name', 'Supplier');
            //         })
            // ),
            // 'admin' => Tab::make()->query(
            //     fn($query) => $query
            //         ->whereHas('roles', function ($query) {
            //             $query->where('name', 'Admin');
            //         })
            // ),
            //  'pending request' => Tab::make()->query(fn ($query) => $query->where('new_supplier_request', true)),
        ];
    }
}
