<?php

namespace App\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsActivityWithTenant
{
    use LogsActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->tenant_id = $this->tenant_id;
    }

    public function getActivitylogOptions(): LogOptions
    {
        $logName = $this->getLogName() ?? 'default';

        return LogOptions::defaults()
            ->useLogName($logName)
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['tenant_id', 'previous_version', 'provider_metadata', 'phone_number']);
    }

    protected function getLogName(): ?string
    {
        /** @phpstan-ignore function.alreadyNarrowedType */
        return property_exists($this, 'logName') ? $this->logName : null;
    }
}
