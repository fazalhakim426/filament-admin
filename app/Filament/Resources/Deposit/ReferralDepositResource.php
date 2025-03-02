<?php

namespace App\Filament\Resources\Deposit; 

use App\Filament\Resources\Deposit\ReferralDepositResource\Pages\ListReferralDeposits;
use App\Models\Deposit;
use Filament\Resources\Resource;
use Filament\Tables\Table; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ReferralDepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $label = 'Referral Deposit';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $recordTitleAttribute = 'transaction_reference';
    public static function canCreate(): bool
    {
        return false; // Disables the "Create" button
    }

    public static function table(Table $table): Table
    {
        return $table->query(Deposit::referralDeposits()) 
        ->columns([
            TextColumn::make('transaction_reference')->sortable(),
            TextColumn::make('user.name')->label('Reseller')->sortable(),
            TextColumn::make('referral.referral_code')->label('Referral')->sortable(), 
            TextColumn::make('amount')->money('PKR')->sortable(), 
            TextColumn::make('transaction_type') 
            ->badge()
            ->label('Type')
            ->color(fn ($record) => $record->transaction_type === 'credit' ? 'success' : 'danger'),
            TextColumn::make('deposit_type')->label('type')->sortable(),   
            TextColumn::make('balance')->sortable(),
            TextColumn::make('created_at')->label('Date')->date(),
        ]) ->filters([
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
            'index' => ListReferralDeposits::route('/'),
        ];
    }
}
