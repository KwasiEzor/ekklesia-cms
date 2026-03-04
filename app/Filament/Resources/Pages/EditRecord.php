<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord as BaseEditRecord;
use Filament\Schemas\Schema;

abstract class EditRecord extends BaseEditRecord
{
    public function defaultForm(Schema $schema): Schema
    {
        if (! $schema->hasCustomColumns()) {
            $schema->columns(1);
        }

        return $schema
            ->inlineLabel($this->hasInlineLabels())
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data');
    }
}
