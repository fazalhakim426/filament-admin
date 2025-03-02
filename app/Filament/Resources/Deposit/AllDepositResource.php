<?php

namespace App\Filament\Resources\Deposit; 

use App\Filament\Resources\Deposit\AllDepositResource\Pages\ListAllDeposits;
use App\Models\Deposit;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
class AllDepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $label = 'All Deposit'; 
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $recordTitleAttribute = 'transaction_reference';
    public static function canCreate(): bool
    {
        return false; // Disables the "Create" button
    }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('transaction_reference')->nullable(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\Select::make('transaction_type')->options([
                'debit' => 'Debit',
                'credit' => 'Credit',
            ])->required(),
            Forms\Components\Select::make('deposit_type')->options([
                'order' => 'Order',
                'referral' => 'Referral',
                'cash_in' => 'Cash In',
                'cash_out' => 'Cash Out',
            ])->required(),
            Forms\Components\TextInput::make('currency')->default('PKR'),
            Forms\Components\Textarea::make('description')->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('transaction_reference')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('amount')->sortable(),
            Tables\Columns\TextColumn::make('deposit_type')->sortable(),
            
            TextColumn::make('transaction_type') 
            ->badge()
            ->label('Type')
            ->color(fn ($record) => $record->transaction_type === 'credit' ? 'success' : 'danger'),
            
            Tables\Columns\TextColumn::make('balance')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->sortable()->date(),
        ])->defaultSort('created_at','DESC')
        ->filters([
            SelectFilter::make('user_id')
                ->label('Filter by User')
                ->relationship('user', 'name') // Automatically fetch user names
                ->searchable()
                ->preload(), // Preloads options to avoid extra queries
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAllDeposits::route('/'),
        ];
    }
}
