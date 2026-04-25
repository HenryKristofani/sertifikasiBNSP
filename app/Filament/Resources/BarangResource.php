<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BarangExporter;
use App\Filament\Resources\BarangResource\Pages;
use App\Models\Barang;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Set;
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

    protected static function updateHargaTotal(Set $set, Get $get): void
    {
        $hargaPerItem = (int) ($get('harga_per_item') ?? 0);
        $stok = (int) ($get('stok') ?? 0);

        $set('harga', $hargaPerItem * $stok);
    }

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Data Barang';
    protected static ?string $modelLabel = 'Barang';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('kode_barang')
                ->label('Kode Barang')
                ->maxLength(255)
                ->required(),

            TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required(),

            Select::make('kategori')
                ->label('Kategori')
                ->options([
                    'Elektronik'  => 'Elektronik',
                ])
                ->required(),

            TextInput::make('stok')
                ->label('Stok')
                ->numeric()
                ->minValue(0)
                ->live(onBlur: true)
                ->afterStateUpdated(fn(Set $set, Get $get) => static::updateHargaTotal($set, $get))
                ->required(),

            TextInput::make('harga_per_item')
                ->label('Harga per Item (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->live(onBlur: true)
                ->afterStateHydrated(function (TextInput $component, ?Barang $record): void {
                    if (! $record || (int) $record->stok <= 0) {
                        return;
                    }

                    $component->state((int) ($record->harga / $record->stok));
                })
                ->afterStateUpdated(fn(Set $set, Get $get) => static::updateHargaTotal($set, $get))
                ->required(),

            TextInput::make('harga')
                ->label('Harga Total (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->disabled()
                ->dehydrated()
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
                TextColumn::make('no')->label('No')->rowIndex(),
                TextColumn::make('kode_barang')->label('Kode Barang')->searchable()->sortable(),
                TextColumn::make('nama_barang')->label('Nama Barang')->searchable()->sortable(),
                TextColumn::make('kategori')->label('Kategori')->badge(),
                TextColumn::make('stok')->label('Stok')->sortable(),
                TextColumn::make('harga')->label('Harga Total')->money('IDR')->sortable(),
                TextColumn::make('keterangan')->label('Keterangan')->wrap(),
                TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Tanggal Diedit')->dateTime()->sortable(),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Ekspor Excel')
                    ->exporter(BarangExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ]),
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
