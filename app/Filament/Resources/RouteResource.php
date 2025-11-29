<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouteResource\Pages;
use App\Filament\Resources\RouteResource\RelationManagers;
use App\Models\Route;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\Port;
use App\Models\TicketCategory;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- BAGIAN INFO RUTE ---
                Select::make('origin_port_id')
                    ->label('Pelabuhan Asal')
                    ->options(Port::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('destination_port_id')
                    ->label('Pelabuhan Tujuan')
                    ->options(Port::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->different('origin_port_id'), // Asal & Tujuan gaboleh sama

                TextInput::make('duration_minutes')
                    ->label('Durasi (Menit)')
                    ->numeric()
                    ->default(60)
                    ->required(),

                // --- BAGIAN HARGA (REPEATER) ---
                Repeater::make('pricings')
                    ->relationship() // Ini magic-nya, connect ke model pricings
                    ->schema([
                        Select::make('category_id')
                            ->label('Golongan Tiket')
                            ->options(TicketCategory::all()->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull() // Lebarkan ke samping
                    ->label('Daftar Harga Tiket per Golongan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('origin.name')->label('Asal')->sortable(),
                Tables\Columns\TextColumn::make('destination.name')->label('Tujuan')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->suffix(' Menit'),
                Tables\Columns\TextColumn::make('pricings_count')->counts('pricings')->label('Jml. Kategori Harga'),
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
            'index' => Pages\ListRoutes::route('/'),
            'create' => Pages\CreateRoute::route('/create'),
            'edit' => Pages\EditRoute::route('/{record}/edit'),
        ];
    }
}
