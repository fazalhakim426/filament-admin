<?php

namespace App\Filament\Resources\Shop\CustomerResource\RelationManagers;

use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Country;
use App\Models\State;
use Filament\Forms\Components\Select;

class ReferralRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->tel(),

                Forms\Components\TextInput::make('street'),

                Forms\Components\TextInput::make('zip'),

                // Country Dropdown
                Select::make('country_id')
                    ->label('Country')
                    ->searchable()
                    ->relationship(name: 'country', titleAttribute: 'name')
                    ->preload()
                    ->live(), // Makes it reactive to update state dropdown

                // State Dropdown (Dependent on Country)
                Select::make('state_id')
                    ->label('State')
                    ->searchable()
                    ->options(
                        fn(callable $get) =>
                        $get('country_id')
                            ? State::where('country_id', $get('country_id'))->pluck('name', 'id')
                            : []
                    )
                    ->preload()
                    ->live()
                    ->disabled(fn(callable $get) => empty($get('country_id'))), // Disable if no country is selected

                // City Dropdown (Dependent on State)
                Select::make('city_id')
                    ->label('City')
                    ->searchable()
                    ->options(
                        fn(callable $get) =>
                        $get('state_id')
                            ? City::where('state_id', $get('state_id'))->pluck('name', 'id')
                            : []
                    )
                    ->preload()
                    ->live()
                    ->disabled(fn(callable $get) => empty($get('state_id'))), // Disable if no state is selected
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([ 
                Tables\Columns\TextColumn::make('reseller.name'),
                Tables\Columns\TextColumn::make('supplier.name'),
                Tables\Columns\TextColumn::make('orderItem.order.warehouse_number'),
                Tables\Columns\TextColumn::make('orderItem.product.name'), 
                Tables\Columns\IconColumn::make('reward_released')
                    ->label('Released')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reward_amount'),
                Tables\Columns\TextColumn::make('referral_code'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\AttachAction::make(),
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(), 
                // Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                // Tables\Actions\DetachBulkAction::make(),
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
