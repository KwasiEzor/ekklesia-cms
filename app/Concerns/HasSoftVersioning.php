<?php

namespace App\Concerns;

trait HasSoftVersioning
{
    public static function bootHasSoftVersioning(): void
    {
        static::updating(function ($model) {
            $dirty = $model->getDirty();

            // Don't snapshot if only previous_version itself is changing (revert operation)
            unset($dirty['updated_at'], $dirty['previous_version']);

            if (! empty($dirty)) {
                $snapshot = [];
                foreach (array_keys($dirty) as $key) {
                    $snapshot[$key] = $model->getOriginal($key);
                }
                $snapshot['_versioned_at'] = now()->toIso8601String();
                $model->previous_version = $snapshot;
            }
        });
    }

    public function hasPreviousVersion(): bool
    {
        return ! empty($this->previous_version);
    }

    public function revertToPreviousVersion(): bool
    {
        if (! $this->hasPreviousVersion()) {
            return false;
        }

        $snapshot = $this->previous_version;
        unset($snapshot['_versioned_at']);

        foreach ($snapshot as $key => $value) {
            $this->{$key} = $value;
        }

        $this->previous_version = null;
        $this->saveQuietly();

        return true;
    }
}
