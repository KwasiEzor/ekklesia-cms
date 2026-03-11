<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;
}
