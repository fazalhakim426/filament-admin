<?php

namespace App\Filament\Resources\Shop;

use App\Events\UserCreated;
use App\Filament\Resources\Shop\SupplierResource\Pages;
use App\Models\City;
use App\Models\Role;
use App\Models\State;
use App\Models\SubCategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class SupplierResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Supplier';

    protected static ?string $slug = 'shop/supplier';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $title = 'Supplier';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->whereHas('roles', function ($query) {
            $query->where('name', 'Supplier')
                ->orWhere('name', 'Super Admin');
        })->withoutGlobalScope(SoftDeletingScope::class);
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'User registered';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email'),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),

                        // Country Dropdown
                        Select::make('country_id')
                            ->label('Country')
                            ->searchable()
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->preload()
                            ->live(),

                        // State Dropdown (Dependent on Country)
                        Select::make('state_id')
                            ->label('State')
                            ->searchable()
                            ->options(
                                fn(callable $get) =>
                                $get('country_id')
                                    ? State::where('country_id', $get('country_id'))->pluck('name', 'id')
                                    : []
                            )
                            ->preload()
                            ->live()
                            ->disabled(fn(callable $get) => empty($get('country_id'))),

                        // City Dropdown (Dependent on State)
                        Select::make('city_id')
                            ->label('City')
                            ->searchable()
                            ->options(
                                fn(callable $get) =>
                                $get('state_id')
                                    ? City::where('state_id', $get('state_id'))->pluck('name', 'id')
                                    : []
                            )
                            ->preload()
                            ->live()
                            ->disabled(fn(callable $get) => empty($get('state_id'))),

                        FileUpload::make('profile_photo_path')
                            ->directory('images')
                            ->disk('public')
                            ->visibility('public'),

                        Forms\Components\Select::make('roles')
                            ->relationship(name: 'roles', titleAttribute: 'name')
                            ->saveRelationshipsUsing(function (Model $record, $state) {
                                $record->roles()->sync($state);
                            })
                            ->options(
                                Role::whereIn('name', ['Supplier', 'Admin'])->pluck('name', 'id')

                            )
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Toggle::make('active')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Supplier Details')
                    ->relationship('supplierDetail') // Ensure relation is properly handled
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->label('Business Name')
                            ->required(),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person'),
                        Forms\Components\TextInput::make('website')
                            ->label('Website'),
                        Select::make('supplier_type')
                            ->label('Supplier Type')
                            ->options([
                                'wholesale' => 'Wholesale',
                                'retail' => 'Retail',
                                'distributor' => 'Distributor',
                            ]),

                        Select::make('ecommerce_experience')
                            ->label('E-commerce Experience')
                            ->options([
                                'none' => 'None',
                                '1-3 years' => '1-3 Years',
                                '3-5 years' => '3-5 Years',
                                '5+ years' => '5+ Years',
                            ]),

                        // Category and Sub-category with proper relation
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship(name: 'category', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->live(),

                        Select::make('sub_category_id')
                            ->label('Sub Category')
                            ->options(
                                fn(callable $get) =>
                                $get('category_id')
                                    ? SubCategory::where('category_id', $get('category_id'))->pluck('name', 'id')
                                    : []
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn(callable $get) => empty($get('category_id'))),

                        Forms\Components\TextInput::make('product_available')
                            ->label('Products Available')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('product_source')
                            ->label('Product Source'),
                        Forms\Components\TextInput::make('product_unit_quality')
                            ->label('Product Unit Quality'),


                        Forms\Components\TextInput::make('product_range')
                            ->label('Product Range'),

                        Forms\Components\TextInput::make('marketing_type')
                            ->label('Marketing Type'),

                        Forms\Components\DateTimePicker::make('preferred_contact_time')
                            ->label('Preferred Contact Time'),


                        Forms\Components\Toggle::make('self_listing')
                            ->label('Self Listing'),
                        Forms\Components\Toggle::make('term_agreed')
                            ->label('Terms Agreed'),

                        Forms\Components\Toggle::make('using_daraz')
                            ->label('Using Daraz')
                            ->live(), // Ensure it updates the form dynamically

                        Forms\Components\TextInput::make('daraz_url')
                            ->label('Daraz URL')
                            ->hidden(fn(callable $get) => !$get('using_daraz')), // Hide if not checked
                    ])
                    ->columns(3)
                    ->columnSpan(['lg' => 3]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?User $record) => $record === null),
            ])
            ->columns(3);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')->label('Photo')->default('https://ui-avatars.com/api/?name=X&color=FFFFFF&background=020617'),
                TextColumn::make('supplierDetail.business_name')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->sortable()->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('city.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roles')
                    ->badge()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state) // Combine roles as a comma-separated string
                    ->color('primary') // Optional: Set a default color for badges
                    ->extraAttributes(['class' => 'space-x-1']) // Adds spacing between badges

                    ->getStateUsing(fn(User $record) => $record->roles->pluck('name')->join(', ')), // Fetch roles and join them as a string

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    /** @return Builder<User> */


    public static function getRelations(): array
    {
        return [
            // RecipientRelationManager::class,
            // PaymentsRelationManager::class,
            // OrdersRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
