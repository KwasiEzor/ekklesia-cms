<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Exports\MemberExporter;
use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(MemberExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
