<?php

namespace App\Filament\Exports;

use App\Models\GivingRecord;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class GivingRecordExporter extends Exporter
{
    protected static ?string $model = GivingRecord::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('member.full_name')
                ->label(__('giving_records.member')),
            ExportColumn::make('amount')
                ->label(__('giving_records.amount')),
            ExportColumn::make('currency')
                ->label(__('giving_records.currency')),
            ExportColumn::make('date')
                ->label(__('giving_records.date')),
            ExportColumn::make('method')
                ->label(__('giving_records.method')),
            ExportColumn::make('reference')
                ->label(__('giving_records.reference')),
            ExportColumn::make('fund.name')
                ->label(__('giving_records.fund')),
            ExportColumn::make('campaign.name')
                ->label(__('giving_records.campaign')),
            ExportColumn::make('is_voided')
                ->label(__('giving_records.voided'))
                ->state(fn (GivingRecord $record): string => $record->adjustments()->where('type', 'void')->exists() ? 'YES' : 'NO'),
            ExportColumn::make('created_at')
                ->label(__('giving_records.created_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your giving record export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
