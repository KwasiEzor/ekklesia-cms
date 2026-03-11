<?php

namespace App\Jobs;

use App\Models\BulkMessage;
use App\Models\NotificationDispatch;
use App\Services\Notification\NotificationChannelManager;
use App\Services\Notification\NotificationPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public BulkMessage $bulkMessage,
    ) {}

    public function handle(NotificationChannelManager $channelManager): void
    {
        $this->bulkMessage->markAsSending();

        $recipients = $this->bulkMessage->getRecipients();
        $this->bulkMessage->update(['recipient_count' => $recipients->count()]);

        $channel = $channelManager->driver($this->bulkMessage->channel);
        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $member) {
            $recipient = $this->bulkMessage->channel === 'sms' ? $member->phone : $member->email;

            if (! $recipient) {
                $failedCount++;

                continue;
            }

            $payload = new NotificationPayload(
                recipient: $recipient,
                body: $this->bulkMessage->body,
                subject: $this->bulkMessage->title,
                type: 'bulk_message',
                memberId: $member->id,
                metadata: ['bulk_message_id' => $this->bulkMessage->id],
            );

            try {
                $channel->send($payload);

                NotificationDispatch::create([
                    'member_id' => $member->id,
                    'channel' => $this->bulkMessage->channel,
                    'type' => 'bulk_message',
                    'status' => 'sent',
                    'recipient' => $recipient,
                    'subject' => $this->bulkMessage->title,
                    'body' => $this->bulkMessage->body,
                    'metadata' => ['bulk_message_id' => $this->bulkMessage->id],
                    'sent_at' => now(),
                    'tenant_id' => $this->bulkMessage->tenant_id,
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                NotificationDispatch::create([
                    'member_id' => $member->id,
                    'channel' => $this->bulkMessage->channel,
                    'type' => 'bulk_message',
                    'status' => 'failed',
                    'recipient' => $recipient,
                    'subject' => $this->bulkMessage->title,
                    'body' => $this->bulkMessage->body,
                    'metadata' => ['bulk_message_id' => $this->bulkMessage->id],
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                    'tenant_id' => $this->bulkMessage->tenant_id,
                ]);

                $failedCount++;
            }
        }

        $this->bulkMessage->markAsSent($sentCount, $failedCount);
    }
}
