<?php

namespace App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use App\Events\UserCreated;
use Illuminate\Support\Str;
use Filament\Actions;
class ManageEmployees extends ManageRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function ($record) {
                    $record->assignRole('employee'); 
                    // event(new UserCreated($record, Str::random(8)));  
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', false)),
        ];
    }
}
