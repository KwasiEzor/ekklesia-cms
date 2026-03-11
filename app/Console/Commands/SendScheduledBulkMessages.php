<?php

namespace App\Console\Commands;

use App\Jobs\SendBulkMessageJob;
use App\Models\BulkMessage;
use App\Models\Tenant;
use Illuminate\Console\Command;

class SendScheduledBulkMessages extends Command
{
    protected $signature = 'bulk-messages:send-scheduled';

    protected $description = 'Send all scheduled bulk messages that are due';

    public function handle(): int
    {
        $count = 0;

        foreach (Tenant::all() as $tenant) {
            tenancy()->initialize($tenant);

            $messages = BulkMessage::readyToSend()->get();

            foreach ($messages as $message) {
                SendBulkMessageJob::dispatch($message);
                $count++;
            }

            tenancy()->end();
        }

        $this->info("Dispatched {$count} scheduled bulk messages.");

        return self::SUCCESS;
    }
}
