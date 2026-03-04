<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;
use Filament\Schemas\Schema;

abstract class CreateRecord extends BaseCreateRecord
{
    public function defaultForm(Schema $schema): Schema
    {
        if (! $schema->hasCustomColumns()) {
            $schema->columns(1);
        }

        return $schema
            ->inlineLabel($this->hasInlineLabels())
            ->model($this->getModel())
            ->statePath('data')
            ->operation('create');
    }
}
