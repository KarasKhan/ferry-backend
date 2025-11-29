<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilih User pembeli
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name') // Menampilkan nama user
                    ->required()
                    ->searchable()
                    ->preload(),

                // Pilih Jadwal (Sementara kita tampilkan ID dulu agar tidak error)
                Forms\Components\Select::make('schedule_id')
                    ->relationship('schedule', 'id') 
                    ->required(),

                // Total Harga
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                // Status Pembayaran
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                    ])
                    ->default('pending')
                    ->required(),
                
                // Metode Pembayaran
                Forms\Components\TextInput::make('payment_method'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Kode Booking (Bisa dicari & dicopy)
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Kode Booking')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold'),

                // 2. Nama Penumpang/User
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemesan')
                    ->searchable(),

                // 3. Rute (Custom Format: Asal -> Tujuan)
                Tables\Columns\TextColumn::make('schedule.route.origin.name')
                    ->label('Rute Perjalanan')
                    ->formatStateUsing(fn ($record) => 
                        $record->schedule->route->origin->name . ' ➝ ' . $record->schedule->route->destination->name
                    )
                    ->description(fn ($record) => 
                        \Carbon\Carbon::parse($record->schedule->departure_time)->format('d M Y, H:i') . ' WIB'
                    ),

                // 4. Total Harga (Format Rupiah)
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('IDR') // Otomatis format Rp
                    ->sortable(),

                // 5. Status Pembayaran (Pakai Warna)
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'danger',
                    })
                    ->sortable(),
                
                // 6. Tanggal Transaksi
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc') // Yang terbaru paling atas
            ->filters([
                // Filter Status (Biar gampang cari yang belum bayar)
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'paid' => 'Lunas (Paid)',
                        'pending' => 'Belum Bayar (Pending)',
                        'cancelled' => 'Batal',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tambahan: Tombol Delete kalau mau hapus data sampah
                Tables\Actions\DeleteAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
