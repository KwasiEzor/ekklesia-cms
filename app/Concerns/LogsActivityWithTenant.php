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

        if (isset($activity->properties['attributes']['custom_fields'])) {
            $properties = $activity->properties->toArray();
            $properties['attributes']['custom_fields'] = $this->scrubPii($properties['attributes']['custom_fields']);

            if (isset($properties['old']['custom_fields'])) {
                $properties['old']['custom_fields'] = $this->scrubPii($properties['old']['custom_fields']);
            }

            $activity->properties = collect($properties);
        }
    }

    protected function scrubPii(array $data): array
    {
        $sensitiveKeys = [
            'phone', 'telephone', 'mobile', 'email', 'address', 'addr',
            'amount', 'montant', 'price', 'prix', 'birth', 'naissance',
        ];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->scrubPii($value);

                continue;
            }

            foreach ($sensitiveKeys as $sensitiveKey) {
                if (str_contains(strtolower((string) $key), $sensitiveKey)) {
                    $data[$key] = '[REDACTED]';
                    break;
                }
            }
        }

        return $data;
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
