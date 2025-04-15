<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\CategoryResource\Pages;
use App\Filament\Resources\Settings\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Category';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->nullable(),
                FileUpload::make('image')
                    ->image()
                    ->nullable(),
                    Repeater::make('images')
                    ->label('Category banners')
                    ->relationship('images')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'image' => 'Image',
                                'video' => 'Video',
                            ])
                            ->default('image')
                            ->required(),

                        FileUpload::make('url')
                            ->label('Upload File')
                            ->preserveFilenames()
                            ->directory('products/media')
                            ->required()
                            ->image()
                            ->acceptedFileTypes(['image/*', 'video/mp4', 'video/avi', 'video/mov', 'video/webm'])
                            ->maxSize(102400),
                    ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->circular()->default('https://ui-avatars.com/api/?name=C&color=FFFFFF&background=020617'),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(50),
            ])
            ->defaultSort('id','desc')

            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
