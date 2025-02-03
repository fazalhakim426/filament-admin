<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages; 
use App\Models\Deposit;
use App\Models\Referral; 
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput; 
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $sort = 7;

    public static function canCreate(): bool
    {
        return false; // Disables the "Create" button
    } 


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reseller.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orderItem.product.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orderItem.order.warehouse_number')
                    ->searchable(),
                Tables\Columns\IconColumn::make('reward_released')
                    ->label('Released')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reward_amount')
                    ->label('Amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                


                
                Action::make('Pay')
                    ->modalHeading('Make a Payment')
                    ->modalButton('Pay Now')
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        // Show User Balance
                        Placeholder::make('balance')
                            ->label('User Balance')
                            ->content(fn(Referral $record) => 'PKR ' . number_format($record->user->balance ?? 0, 2)),
                
                      
                        // Show Total Paid and Need to Pay
                        // Placeholder::make('paid')
                        //     ->label('Total Paid')
                        //     ->content(fn(Referral $record) => 'PKR ' . number_format($record->paid, 2)),
                
                        // Placeholder::make('need_to_pay')
                        //     ->label('Amount Due')
                        //     ->content(fn(Referral $record) => 'PKR ' . number_format($record->need_to_pay, 2)),
                
                        // Payment Input
                        TextInput::make('amount')
                        ->label('Amount to Pay')
                        ->numeric()
                        ->default(fn(Referral $record) => $record->reward_amount)
                        ->required()
                        ->disabled(),
                         
                
                        Textarea::make('description')->label('Description'),
                    ])
                    ->action(fn(array $data, Referral $record) => self::handlePayment($record, $record->reward_amount, $data['description']))
                    ->visible(fn(Referral $record) => $record->reward_amount > 0 && !$record->reward_released),
                

            ])
            ->bulkActions([]);
    }
    protected static function handlePayment(Referral $referral, $amount, $description = null)
    {
        $referral->update(['reward_released'=>true]);
        Deposit::create([ 
            'user_id' => $referral->reseller_user_id,
            'referral_id' => $referral->id,
            'amount' => $amount,
            'transaction_type' => 'credit',
            'deposit_type' => 'wallet',
            'currency' => 'PKR',
            'provider' => 'Account Balance', 
            'description' => $description,
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
        ];
    }
}
