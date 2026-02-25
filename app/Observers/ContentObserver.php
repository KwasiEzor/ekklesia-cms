<?php

namespace App\Observers;

use App\Events\ContentChanged;
use Illuminate\Database\Eloquent\Model;

class ContentObserver
{
    public function created(Model $model): void
    {
        ContentChanged::dispatch($model, 'created', $this->currentUserName());
    }

    public function updated(Model $model): void
    {
        ContentChanged::dispatch($model, 'updated', $this->currentUserName());
    }

    public function deleted(Model $model): void
    {
        ContentChanged::dispatch($model, 'deleted', $this->currentUserName());
    }

    private function currentUserName(): ?string
    {
        return auth()->user()?->name;
    }
}
