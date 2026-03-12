<?php

namespace App\Filament\Exports;

use App\Models\Member;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class MemberExporter extends Exporter
{
    protected static ?string $model = Member::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('first_name')
                ->label(__('members.first_name')),
            ExportColumn::make('last_name')
                ->label(__('members.last_name')),
            ExportColumn::make('email')
                ->label(__('members.email')),
            ExportColumn::make('phone')
                ->label(__('members.phone')),
            ExportColumn::make('baptism_date')
                ->label(__('members.baptism_date')),
            ExportColumn::make('status')
                ->label(__('members.status')),
            ExportColumn::make('cellGroup.name')
                ->label(__('members.cell_group')),
            ExportColumn::make('created_at')
                ->label(__('members.created_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your member export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
