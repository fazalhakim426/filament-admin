<?php

namespace App\Filament\Resources\Shop;

use App\Enums\OrderStatus; 
use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\RelationManagers;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;
use App\Forms\Components\AddressForm;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;

use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Squire\Models\Currency; 
use Filament\Forms\Components\Placeholder; 
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'warehouse_number';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),
                        Forms\Components\Section::make('Order items')
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn(Forms\Set $set) => $set('items', [])),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Order $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(Order $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(Order $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Order $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('warehouse_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customerUser.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record) => $record->status === 'pending' ? 'gray' : ($record->status === 'confirmed' ? 'primary' : ($record->status === 'shipped' ? 'success' : ($record->status === 'delivered' ? 'success' : ($record->status === 'canceled' || $record->status === 'refunded' ? 'danger' : 'gray'))))),

                // Tables\Columns\TextColumn::make('currency')
                //     ->getStateUsing(fn ($record): ?string => Currency::find($record->currency)?->name ?? null)
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('PKR')
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('PKR')
                    ->label('Shipping Cost')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Action::make('Pay')
                //     ->modalHeading('Make a Payment')
                //     ->modalButton('Pay Now')
                //     ->icon('heroicon-o-currency-dollar')
                //     ->form([
                //         Forms\Components\TextInput::make('amount')
                //             ->label('Amount to Pay')
                //             ->numeric()

                //             ->default(fn(Order $record) => $record->need_to_pay) // Prefill with need_to_pay
                //             ->required(),
                             
                //         Forms\Components\Textarea::make('description')->label('Description'),
                //     ])
                //     ->action(fn(array $data, Order $record) => self::handlePayment($record, $data['amount'], $data['description']))
                //     ->visible(fn(Order $record) => $record->need_to_pay > 0),





                
                Action::make('Pay')
                    ->modalHeading('Make a Payment')
                    ->modalButton('Pay Now')
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        // Show User Balance
                        Placeholder::make('balance')
                            ->label('User Balance')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->user->balance ?? 0, 2)),
                
                        // Show Order Deposit History
                        // Repeater::make('order_deposits')
                        //     ->label('Order Deposit History')
                        //     ->relationship('deposits') // Assuming an Order hasMany Deposits
                        //     ->schema([
                        //         Placeholder::make('transaction_reference')->label('Transaction Ref'),
                        //         // Placeholder::make('amount')->label('Amount')->content(fn($record) => 'PKR ' . number_format($record->amount, 2)),
                        //         Placeholder::make('transaction_type')->label('Type'),
                        //         Placeholder::make('deposit_type')->label('Deposit Type'),
                        //         Placeholder::make('description')->label('Description'),
                        //         // Placeholder::make('created_at')->label('Date')->content(fn($record) => $record->created_at->format('Y-m-d H:i')),
                        //     ])
                        //     ->collapsed(),
                
                        // Show Total Paid and Need to Pay
                        Placeholder::make('paid')
                            ->label('Total Paid')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->paid, 2)),
                
                        Placeholder::make('need_to_pay')
                            ->label('Amount Due')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->need_to_pay, 2)),
                
                        // Payment Input
                        TextInput::make('amount')
                            ->label('Amount to Pay')
                            ->numeric()
                            ->default(fn(Order $record) => $record->need_to_pay)
                            ->required(),
                
                        Textarea::make('description')->label('Description'),
                    ])
                    ->action(fn(array $data, Order $record) => self::handlePayment($record, $data['amount'], $data['description']))
                    ->visible(fn(Order $record) => $record->need_to_pay > 0),
                



                Action::make('Refund')
                    ->modalHeading('Refund Payment')
                    ->modalButton('Refund Now')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->form([
                        
                        Placeholder::make('balance')
                        ->label('User Balance')
                        ->content(fn(Order $record) => 'PKR ' . number_format($record->user->balance ?? 0, 2)),
                        // Show Total Paid and Need to Pay
                        Placeholder::make('paid')
                            ->label('Total Paid')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->paid, 2)),
                
                        Placeholder::make('need_to_pay')
                            ->label('Amount Due')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->need_to_pay, 2)),
                

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount to Refund')
                            ->numeric()                            
                            ->default(fn(Order $record) => $record->paid) // Prefill with need_to_pay
                            ->required(),
                        Forms\Components\Textarea::make('description')->label('Reason for Refund'),
                    ])
                    ->action(fn(array $data, Order $record) => self::handleRefund($record, $data['amount'], $data['description']))
                    ->visible(fn(Order $record) => $record->paid > 0),

            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['warehouse_number', 'customerUser.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'name' => optional($record->customerUser)->name,
        ];
    }

    /** @return Builder<Order> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customerUser', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 'new')->count();
    }

    /** @return Forms\Components\Component[] */
    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('warehouse_number')
                ->default('WH' . random_int(100000000, 999999999) . 'PK')
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(Order::class, 'warehouse_number', ignoreRecord: true),

            Forms\Components\Select::make('customer_user_id')
                ->label('Customer')
                ->relationship('customerUser', 'name', function (Builder $query) {
                    // Filter users with the 'Supplier' role
                    $query->whereHas('roles', function ($query) {
                        $query->where('name', 'customer'); // Assuming your roles are stored by name
                    });
                })
                ->searchable()
                ->required()
                ->preload()
                ->reactive(),
            Forms\Components\Select::make('sender_id')
                ->relationship('sender', 'name')
                ->searchable()
                ->required()
                ->preload()
                ->options(fn(callable $get) => \App\Models\Address::where('user_id', $get('customer_user_id'))->pluck('name', 'id'))
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->email()->required(),
                    Forms\Components\TextInput::make('phone')->nullable(),
                    Forms\Components\TextInput::make('whatsapp')->nullable(),
                    Forms\Components\TextInput::make('address')->required(),
                    Forms\Components\TextInput::make('street')->nullable(),
                    Forms\Components\TextInput::make('zip')->nullable(),
                    Forms\Components\Select::make('country_id')->relationship('country', 'name')->required(),
                    Forms\Components\Select::make('state_id')->relationship('state', 'name')->required(),
                    Forms\Components\Select::make('city_id')->relationship('city', 'name')->required(),
                    Forms\Components\Select::make('user_id')->relationship('user', 'name')->required(),
                ])
                ->reactive(), // Reacts when customer_user_id changes

            Forms\Components\Select::make('recipient_id')
                ->relationship('recipient', 'name')
                ->searchable()
                ->required()
                ->preload()
                ->options(fn(callable $get) => \App\Models\Address::where('user_id', $get('customer_user_id'))->pluck('name', 'id'))
                ->reactive(), // Reacts when customer_user_id changes


            // ->createOptionForm([
            //     Forms\Components\TextInput::make('name')
            //         ->required()
            //         ->maxLength(255),

            //     Forms\Components\TextInput::make('email')
            //         ->label('Email address')
            //         ->required()
            //         ->email()
            //         ->maxLength(255)
            //         ->unique(),

            //     Forms\Components\TextInput::make('phone')
            //         ->maxLength(255),

            //     // Forms\Components\Select::make('gender')
            //     //     ->placeholder('Select gender')
            //     //     ->options([
            //     //         'male' => 'Male',
            //     //         'female' => 'Female',
            //     //     ])
            //     //     ->required()
            //     //     ->native(false),
            // ])
            // ->createOptionAction(function (Action $action) {
            //     return $action
            //         ->modalHeading('Create customer')
            //         ->modalSubmitActionLabel('Create customer')
            //         ->modalWidth('lg');
            // })

            Forms\Components\ToggleButtons::make('status')
                ->inline()
                ->options(OrderStatus::class)
                ->required(),

            // Forms\Components\Select::make('currency')
            //     ->searchable()
            //     ->getSearchResultsUsing(fn (string $query) => Currency::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
            //     ->getOptionLabelUsing(fn ($value): ?string => Currency::firstWhere('id', $value)?->getAttribute('name'))
            //     ->required(),

            // AddressForm::make('address')
            //     ->columnSpan('full'),

            // Forms\Components\MarkdownEditor::make('notes')
            //     ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('order_items')
            ->relationship('items')
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $product = Product::find($state);
                        // Ensure product exists and has a supplier
                        if ($product && $product->supplier_user_id) {
                            $set('price', $product->unit_selling_price ?? 0);
                            $set('supplier_user_id', $product->supplier_user_id); // Auto-set supplier ID
                        } else {
                            $set('product_id', null); // Reset product selection if no supplier
                            Notification::make()
                                ->title('This product does not have a supplier. Please add a supplier first.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),

                Forms\Components\Hidden::make('supplier_user_id') // Auto-filled supplier ID
                    ->required(),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }
    protected static function handlePayment(Order $order, $amount, $description = null)
    {
        $order->update(['status'=>'paid']);
        Deposit::create([ 
            'user_id' => $order->customer_user_id,
            'order_id' => $order->id,
            'amount' => $amount,
            'transaction_type' => 'debit',
            'deposit_type' => 'wallet',
            'currency' => 'PKR',
            'provider' => 'Account Balance', 
            'description' => $description,
        ]);
    }

    protected static function handleRefund(Order $order, $amount, $description = null)
    {
        
        $order->update(['status'=>'refunded']);
        Deposit::create([ 
            'user_id' => $order->customer_user_id,
            'order_id' => $order->id,
            'amount' => $amount,
            'transaction_type' => 'credit', // Debit means refunded
            'deposit_type' => 'order_refund',
            'currency' => 'PKR', 
            'description' => $description,
        ]);
    }
}
