<?php

namespace App\Filament\Actions;

use App\Jobs\AiAnalyzeContent;
use Filament\Actions\Action;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class AiAnalyzeAction
{
    public static function make(string $name = 'ai_analyze'): Action
    {
        return Action::make($name)
            ->label(__('ai.analyze_action'))
            ->icon(Heroicon::OutlinedSparkles)
            ->color('warning')
            ->form([
                Components\Select::make('analysis_type')
                    ->label(__('ai.analysis_type'))
                    ->options([
                        'general' => __('ai.analysis_general'),
                        'seo' => __('ai.analysis_seo'),
                        'translation' => __('ai.analysis_translation'),
                    ])
                    ->default('general')
                    ->required(),
            ])
            ->action(function (array $data, $record): void {
                AiAnalyzeContent::dispatch($record, $data['analysis_type']);

                Notification::make()
                    ->title(__('ai.analyze_dispatched'))
                    ->success()
                    ->send();
            });
    }
}
