<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\Ship;
use App\Models\Route;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilih Kapal (Reactive)
                Forms\Components\Select::make('ship_id')
                    ->label('Pilih Kapal')
                    ->options(Ship::all()->pluck('name', 'id'))
                    ->required()
                    ->reactive() // <-- KUNCINYA: Biar bisa mentrigger aksi lain
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Logika: Ambil kapasitas kapal, set ke quota
                        $ship = Ship::find($state);
                        if ($ship) {
                            $set('quota_passenger_left', $ship->capacity_passenger);
                            $set('quota_vehicle_left', $ship->capacity_vehicle);
                        }
                    }),

                // Pilih Rute (Tampilkan Asal - Tujuan)
                Forms\Components\Select::make('route_id')
                    ->label('Pilih Rute')
                    ->options(Route::with(['origin', 'destination'])->get()->mapWithKeys(function ($route) {
                        return [$route->id => $route->origin->name . ' -> ' . $route->destination->name];
                    }))
                    ->searchable()
                    ->required(),

                // Tanggal & Jam
                Forms\Components\DateTimePicker::make('departure_time')
                    ->label('Waktu Berangkat')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('arrival_time')
                    ->label('Estimasi Tiba')
                    ->required(),

                // Status
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open (Buka)',
                        'closed' => 'Closed (Tutup)',
                        'delayed' => 'Delayed (Telat)',
                    ])
                    ->default('open')
                    ->required(),

                // Kuota (Otomatis terisi, tapi bisa diedit)
                Forms\Components\TextInput::make('quota_passenger_left')
                    ->label('Sisa Kuota Penumpang')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('quota_vehicle_left')
                    ->label('Sisa Kuota Kendaraan')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ship.name')->label('Kapal')->searchable(),
                Tables\Columns\TextColumn::make('route.origin.name')->label('Asal'),
                Tables\Columns\TextColumn::make('route.destination.name')->label('Tujuan'),
                Tables\Columns\TextColumn::make('departure_time')->dateTime('d M Y, H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        'delayed' => 'warning',
                        'completed' => 'gray',
                    }),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
