<?php

namespace App\Checks;

use App\Services\Ai\AiManager;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class AiConnectivityCheck extends Check
{
    public function run(): Result
    {
        $result = Result::make()
            ->meta([
                'provider' => config('ai.default'),
            ]);

        try {
            $manager = app(AiManager::class);
            $driver = $manager->driver();

            // Check if API key is configured
            $configKey = 'ai.providers.'.config('ai.default').'.api_key';
            if (empty(config($configKey))) {
                return $result->failed('AI API key is not configured for provider: '.config('ai.default'));
            }

            return $result->notificationMessage('AI Connectivity is configured for '.config('ai.default'))->ok();
        } catch (\Exception $e) {
            return $result->failed('AI Manager initialization failed: '.$e->getMessage());
        }
    }
}
