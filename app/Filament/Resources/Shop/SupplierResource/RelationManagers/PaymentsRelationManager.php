<?php

namespace App\Filament\Resources\Shop\SupplierResource\RelationManagers;

use Akaunting\Money\Currency;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'deposits';
    protected static ?string $label = 'Customers';

    protected static ?string $recordTitleAttribute = 'transaction_reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->relationship(
                        'orders',
                        'warehouse_number',
                        fn(Builder $query, RelationManager $livewire) => $query->whereBelongsTo($livewire->ownerRecord)
                    )
                    ->searchable()
                    ->hiddenOn('edit')
                    ->required(),

                Forms\Components\TextInput::make('transaction_reference')
                    ->columnSpan(fn(string $operation) => $operation === 'edit' ? 2 : 1)
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required(),

                Forms\Components\Select::make('currency')
                    ->searchable()
                    ->required(),

                Forms\Components\ToggleButtons::make('provider')
                    ->inline()
                    ->grouped()
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                    ])
                    ->required(),

                Forms\Components\ToggleButtons::make('deposit_type')
                    ->inline()
                    ->options([
                        'credit_card' => 'Credit card',
                        'bank_transfer' => 'Bank transfer',
                        'paypal' => 'PayPal',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                // Transaction reference
                Tables\Columns\TextColumn::make('transaction_reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.price')
                    ->default('No order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('referral.user.name')
                    ->default('Not referral')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->label('Type')
                    ->color(fn($record) => $record->transaction_type === 'credit' ? 'success' : 'danger') // Conditional badge color
                ,
                Tables\Columns\TextColumn::make('deposit_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([  
            ])
            ->groupedBulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
