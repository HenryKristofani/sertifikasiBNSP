<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Models\Barang;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Data Barang';
    protected static ?string $modelLabel = 'Barang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required(),

            Select::make('kategori')
                ->label('Kategori')
                ->options([
                    'Elektronik'  => 'Elektronik',
                    'ATK'         => 'ATK',
                    'Furniture'   => 'Furniture',
                    'Lainnya'     => 'Lainnya',
                ])
                ->required(),

            TextInput::make('stok')
                ->label('Stok')
                ->numeric()
                ->minValue(0)
                ->required(),

            TextInput::make('harga')
                ->label('Harga (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID Barang')->sortable(),
                TextColumn::make('nama_barang')->label('Nama Barang')->searchable()->sortable(),
                TextColumn::make('kategori')->label('Kategori')->badge(),
                TextColumn::make('stok')->label('Stok')->sortable(),
                TextColumn::make('harga')->label('Harga')->money('IDR')->sortable(),
                TextColumn::make('keterangan')->label('Keterangan')->wrap(),
                TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Tanggal Diedit')->dateTime()->sortable(),
            ])
            ->filters([])
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
            'index'  => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit'   => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}