<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Resources\DepositResource\RelationManagers;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financial';

    protected function afterSave(): void
    {
        // Redirect to the index page
        $this->redirect($this->getResource()::getUrl('index'));
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_reference')
                    ->disabled() // Makes the field read-only
                    ->default(fn() => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(10)))
                    ->maxLength(255),
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()->preload()
                    ->required(),
                // Select::make('order_id')
                //     ->relationship('order','id'),
                // Select::make('referral_id')
                //     ->relationship('referrel','reward_amount'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('transaction_type')
                    ->label('Transaction Type')
                    ->required()
                    ->options([
                        'credit' => 'credit',
                        'debit' => 'debit',
                    ])
                    ->default('credit'), // Optional: Set a default value

                Forms\Components\Select::make('deposit_type')
                    ->label('Deposit Type')
                    ->required()
                    ->options([
                        'bank' => 'Bank',
                        'cash' => 'Cash',
                    ])
                    ->default('Bank'),

                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('transaction_type'),
                Tables\Columns\TextColumn::make('deposit_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            // 'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
