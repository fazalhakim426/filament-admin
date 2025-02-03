<?php

namespace App\Filament\Resources\Deposit;

use App\Filament\Resources\Deposit\CashDepositResource\Pages\CreateCashDeposit;
use App\Filament\Resources\Deposit\CashDepositResource\Pages\EditCashDeposit;
use App\Filament\Resources\Deposit\CashDepositResource\Pages\ListCashDeposits;
use App\Models\Deposit;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\{TextInput, Select, BelongsToSelect, Section}; 
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Tables\Filters\SelectFilter;

class CashDepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static ?string $label = 'Cash Deposit';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financial';
    protected static ?string $recordTitleAttribute = 'transaction_reference';
     
    // public static function table(Table $table): Table
    // {
    //     return $table->query(Deposit::cashInOut()) 
    //     ->columns([
    //         TextColumn::make('transaction_reference')->sortable(),
    //         TextColumn::make('user.name')->label('Deposited By')->sortable(),
    //         TextColumn::make('order.id')->label('Order ID')->sortable(),
    //         TextColumn::make('referral.id')->label('Referral ID')->sortable(),
    //         TextColumn::make('amount')->money('PKR')->sortable(), 
    //         TextColumn::make('transaction_type') 
    //         ->badge()
    //         ->label('Type')
    //         ->color(fn ($record) => $record->transaction_type === 'credit' ? 'success' : 'danger'),
    //         TextColumn::make('deposit_type')->sortable(), 
    //         TextColumn::make('provider')->sortable(),
    //         TextColumn::make('description')->limit(50),
    //         TextColumn::make('created_at')->label('Date')->date(),
    //     ])->filters([
       
    //     ])
    //     ->defaultSort('created_at', 'desc');
    // }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => ListCashDeposits::route('/'),
    //     ];
    // }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Transaction Details')->schema([
                    BelongsToSelect::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required(),

                    Select::make('transaction_type')
                        ->label('Transaction Type')
                        ->options([
                            'credit' => 'Credit',
                            'debit' => 'Debit',
                        ])
                        ->required(),

                    Select::make('deposit_type')
                        ->label('Deposit Type')
                        ->options([
                            'bank_transfer' => 'Bank Transfer',
                            'cash' => 'Cash',
                            'online_payment' => 'Online Payment',
                        ])
                        ->searchable()
                        ->required(),

                    Select::make('currency')
                        ->label('Currency')
                        ->options([
                            'PKR' => 'PKR',
                            'USD' => 'USD',
                            'EUR' => 'EUR',
                        ])
                        ->default('PKR')
                        ->required(),

                    TextInput::make('provider')
                        ->label('Provider')
                        ->nullable(),
 
 

                    TextInput::make('description')
                        ->label('Description')
                        ->nullable(),
                ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->query(Deposit::cashInOut()) 
            ->columns([
                TextColumn::make('user.name')->label('User')->sortable(),
                TextColumn::make('amount')->label('Amount')->sortable(),
                BadgeColumn::make('transaction_type')
                    ->label('Transaction Type')
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ])
                    ->sortable(),
                TextColumn::make('deposit_type')->label('Deposit Type'),
                TextColumn::make('currency')->label('Currency'),
                TextColumn::make('balance')->label('Balance'),
                TextColumn::make('created_at')->label('Date')->dateTime(),
            ])
            ->filters([
            SelectFilter::make('user_id')
                ->label('Filter by User')
                ->relationship('user', 'name') // Automatically fetch user names
                ->searchable()
                ->preload(), // Preloads options to avoid extra queries
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [];
    // }

    public static function getPages(): array
    {
        return [
            'index' => ListCashDeposits::route('/'),
            'create' => CreateCashDeposit::route('/create'),
            'edit' => EditCashDeposit::route('/{record}/edit'),
        ];
    }

}
