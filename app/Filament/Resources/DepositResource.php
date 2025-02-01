<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Filament\Resources\DepositResource\RelationManagers;
use App\Models\Deposit;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $recordTitleAttribute = 'transaction_reference';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Deposited By')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
    
                Select::make('transaction_type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ])
                    ->required(),
    
                TextInput::make('amount')
                    ->numeric()
                    ->required(),
    
                Radio::make('deposit_category')
                    ->label('Deposit Type')
                    ->options([
                        'order' => 'Order Deposit',
                        'referral' => 'Referral Deposit',
                        'user' => 'User Transaction',
                    ])
                    ->live()
                    ->required(),
    
                Select::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->hidden(fn (callable $get) => $get('deposit_category') !== 'order'), // Show only if Order Deposit selected
    
                Select::make('referral_id')
                    ->label('Referral')
                    ->relationship('referral', 'id')
                    ->searchable()
                    ->hidden(fn (callable $get) => $get('deposit_category') !== 'referral'), // Show only if Referral Deposit selected
    
                TextInput::make('deposit_type')
                    ->required(),
    
                TextInput::make('currency')
                    ->default('PKR')
                    ->required(),
    
                TextInput::make('provider')
                    ->nullable(),
    
                Textarea::make('description')
                    ->nullable(),
            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_reference')->sortable(),
                TextColumn::make('user.name')->label('Deposited By')->sortable(),
                TextColumn::make('order.id')->label('Order ID')->sortable(),
                TextColumn::make('referral.id')->label('Referral ID')->sortable(),
                TextColumn::make('amount')->money('PKR')->sortable(), 
                TextColumn::make('transaction_type') 
                ->badge()
                ->label('Type')
                ->color(fn ($record) => $record->transaction_type === 'credit' ? 'success' : 'danger'),
                TextColumn::make('deposit_type')->sortable(), 
                TextColumn::make('provider')->sortable(),
                TextColumn::make('description')->limit(50),
                TextColumn::make('created_at')->label('Date')->date(),
            ])
            ->filters([
                Filter::make('Orders')
                    ->query(fn (Builder $query) => $query->whereNotNull('order_id'))
                    ->label('Order Deposits'),
                    
                Filter::make('Referrals')
                    ->query(fn (Builder $query) => $query->whereNotNull('referral_id'))
                    ->label('Referral Deposits'),
    
                Filter::make('User Transactions')
                    ->query(fn (Builder $query) => $query->whereNull('order_id')->whereNull('referral_id'))
                    ->label('User Transactions'),
            ])
            ->defaultSort('created_at', 'desc');
    }
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDeposits::route('/'),
        ];
    }
}
