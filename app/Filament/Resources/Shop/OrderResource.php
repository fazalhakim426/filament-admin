<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\OrderResource\Pages;
use App\Filament\Resources\Shop\OrderResource\Widgets\OrderStats;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Resources\Resource;
use App\Enums\OrderStatus;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
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
                                \Filament\Forms\Components\Actions\Action::make('reset')
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
                    ->columnSpan(['lg' => fn(?Order $record) => $record === null ? 3 : 3]),

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
            ->columns(2);
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
                Tables\Columns\TextColumn::make('supplierUser.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customerUser.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn($record) => match ($record->payment_status) {
                        'unpaid' => 'gray',
                        'paid' => 'success',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('order_status')
                    ->badge()
                    ->color(fn($record) => match ($record->order_status) {
                        'new' => 'gray',
                        'processing' => 'warning',
                        'confirmed' => 'primary',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_price')
                    ->formatStateUsing(
                        fn($record) =>
                        "PKR " . number_format($record->total_price, 2) .
                            " (Items: PKR " . number_format($record->items_cost, 2) .
                            " + Shipping: PKR " . number_format($record->shipping_cost, 2) . ")"
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('PKR')
                    ->label('Total Price')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('pkr'),
                    ]),
            ])
            ->defaultSort('created_at', 'DESC')
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
                Action::make('view_airway_bill')
                    ->label('AirwayBill')
                    ->url(fn(Order $record) => route('orders.stream-airway-bill', $record)) // adjust route name if needed
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-document-text'),

                Action::make('Pay')
                    ->modalHeading('Make a Payment')
                    ->modalButton('Pay Now')
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        // Show User Balance
                        Placeholder::make('balance')
                            ->label('User Balance')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->customerUser->balance ?? 0, 2)),


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
                    ->visible(fn(Order $record) => $record->need_to_pay > 0 && $record->order_status != OrderStatus::Canceled->value),




                Action::make('Refund')
                    ->modalHeading('Refund Payment')
                    ->modalButton('Refund Now')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->form([
                        Placeholder::make('balance')
                            ->label('User Balance')
                            ->content(fn(Order $record) => 'PKR ' . number_format($record->customerUser->balance ?? 0, 2)),
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
                    ->visible(fn(Order $record) => $record->paid > 0 && !in_array($record->order_status, [OrderStatus::Confirmed->value, OrderStatus::Shipped->value, OrderStatus::Delivered->value])),


                Tables\Actions\EditAction::make(),
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

        return (string) $modelClass::where('order_status', 'new')->count();
    }

    /** @return Forms\Components\Component[] */
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
                ->columnSpan(2)
                ->unique(Order::class, 'warehouse_number', ignoreRecord: true),


            // Add Supplier Select Field here
            Forms\Components\Select::make('supplier_user_id')
                ->label('Supplier')
                ->relationship('supplierUser', 'name', function (Builder $query) {
                    // Filter users with the 'Supplier' role
                    $query->whereHas('roles', function ($query) {
                        $query->where('name', 'supplier'); // Assuming your roles are stored by name
                    });
                })
                ->searchable()
                ->required()
                ->preload()
                ->disabled(fn($get) => $get('order_id') !== null)
                ->reactive(),
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
                ->reactive(),
            Forms\Components\Select::make('recipient_id')
                ->relationship('recipient', 'name')
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

            Forms\Components\ToggleButtons::make('order_status')
                ->inline()
                ->options(OrderStatus::class)
                ->columnSpan(2)
                ->required(),
        ];
    }


    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('order_items')
            ->relationship('items')
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(function (Forms\Get $get) {
                        $supplierUserId = $get('supplier_user_id');
                        if (!$supplierUserId) {
                            return Product::query()->pluck('name', 'id');
                        }
                        return Product::where('supplier_user_id', $supplierUserId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        if ($product && $product->supplier_user_id) {
                            // $set('price', $product->unit_selling_price ?? 0);
                            $set('supplier_user_id', $product->supplier_user_id);
                            $set('product_variant_id', null); // reset variant if product changes
                        } else {
                            $set('product_id', null);
                            Notification::make()
                                ->title('This product does not have a supplier. Please add a supplier first.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan(['md' => 5])
                    ->searchable(),

                // Product Variant Selection
                Forms\Components\Select::make('product_variant_id')
                    ->label('Variant')
                    ->options(function (Forms\Get $get) {
                        $productId = $get('product_id');
                        if (!$productId) return [];
                        return \App\Models\ProductVariant::where('product_id', $productId)
                            ->pluck('sku', 'id'); // Or you can display more detailed label
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $variant = ProductVariant::find($state);
                        if ($variant && $variant->stock_quantity > 0) {
                            $set('price', $variant->unit_selling_price ?? 0);
                            $set('product_variant_id', $variant->id); // reset variant if product changes
                        } else {
                            $set('product_variant_id', null);
                            Notification::make()
                                ->title('Out of stock.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->columnSpan(['md' => 4])
                    ->searchable(),

                // Quantity
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan(['md' => 2])
                    ->required(),

                // Price
                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan(['md' => 3]),

                // Hidden Supplier Field
                Forms\Components\Hidden::make('supplier_user_id')
                    ->required(),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns(['md' => 12])
            ->required();
    }


    protected static function handlePayment(Order $order, $amount, $description = null)
    {
        $order->update(['payment_status' => 'paid']);
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

        $order->update(['payment_status' => 'refunded']);
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
