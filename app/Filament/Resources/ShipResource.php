<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipResource\Pages;
use App\Filament\Resources\ShipResource\RelationManagers;
use App\Models\Ship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShipResource extends Resource
{
    protected static ?string $model = Ship::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Kapal'),
                Forms\Components\TextInput::make('capacity_passenger')
                    ->numeric()
                    ->required()
                    ->label('Kapasitas Penumpang'),
                Forms\Components\TextInput::make('capacity_vehicle')
                    ->numeric()
                    ->default(0)
                    ->label('Kapasitas Kendaraan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('capacity_passenger')->label('Kaps. Penumpang'),
                Tables\Columns\TextColumn::make('capacity_vehicle')->label('Kaps. Kendaraan'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShips::route('/'),
            'create' => Pages\CreateShip::route('/create'),
            'edit' => Pages\EditShip::route('/{record}/edit'),
        ];
    }
}
