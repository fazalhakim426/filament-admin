<?php

namespace App\Filament\Resources\Deposit; 
use App\Filament\Resources\Deposit\OrderDepositResource\Pages\ListOrderDeposits;
use App\Models\Deposit;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class OrderDepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $label = 'Order Deposit';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $recordTitleAttribute = 'transaction_reference';

    
    public static function canCreate(): bool
    {
        return false; // Disables the "Create" button
    }

    public static function table(Table $table): Table
    {
        return $table->query(Deposit::orderDeposits()) // Uses scopeOrderDeposits()
           
        ->columns([
            TextColumn::make('transaction_reference')->sortable(),
            TextColumn::make('user.name')->label('Deposited By')->sortable(),
            TextColumn::make('order.warehouse_number')->label('Warehouse')->sortable(), 
            TextColumn::make('amount')->money('PKR')->sortable(), 
            TextColumn::make('transaction_type') 
            ->badge()
            ->label('Type')
            ->color(fn ($record) => $record->transaction_type === 'credit' ? 'success' : 'danger'),
            TextColumn::make('deposit_type')->sortable(),   
            
            Tables\Columns\TextColumn::make('balance')->sortable(),
            TextColumn::make('created_at')->label('Date')->date(),
        ])
        ->filters([
            SelectFilter::make('user_id')
                ->label('Filter by User')
                ->relationship('user', 'name') // Automatically fetch user names
                ->searchable()
                ->preload(), // Preloads options to avoid extra queries
        ])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderDeposits::route('/'),
        ];
    }
}
