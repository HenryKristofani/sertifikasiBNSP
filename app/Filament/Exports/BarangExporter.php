<?php

namespace App\Filament\Exports;

use App\Models\Barang;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BarangExporter extends Exporter
{
    protected static ?string $model = Barang::class;

    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('kode_barang')->label('Kode Barang'),
            ExportColumn::make('nama_barang')->label('Nama Barang'),
            ExportColumn::make('kategori')->label('Kategori'),
            ExportColumn::make('stok')->label('Stok'),
            ExportColumn::make('harga')->label('Harga Total'),
            ExportColumn::make('keterangan')->label('Keterangan'),
            ExportColumn::make('created_at')->label('Tanggal Dibuat'),
            ExportColumn::make('updated_at')->label('Tanggal Diedit'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your barang export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
