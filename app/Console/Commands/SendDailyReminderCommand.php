<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminder:daily {--user= : Target specific user ID}')]
#[Description('Send daily 17:00 expense recording reminder to all registered web push devices')]
class SendDailyReminderCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WebPushService $webPushService): int
    {
        $userId = $this->option('user');
        $query = PushSubscription::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No active push subscriptions found.');

            return self::SUCCESS;
        }

        $this->info("Sending daily reminder to {$subscriptions->count()} registered device(s)...");

        $payload = [
            'title' => 'Catat Duit 💰',
            'body' => 'Sudah jam 5 sore nih! Jangan lupa catat pengeluaran hari ini ya.',
            'icon' => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-192x192.png',
            'data' => [
                'url' => '/',
                'type' => 'daily-reminder',
            ],
        ];

        $stats = $webPushService->sendToSubscriptions($subscriptions, $payload);

        $this->info("Completed: {$stats['sent']} sent, {$stats['failed']} failed, {$stats['expired']} expired/cleaned.");

        return self::SUCCESS;
    }
}
