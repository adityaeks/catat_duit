<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected ?WebPush $webPush = null;

    protected string $publicKey;

    protected string $privateKey;

    protected string $subject;

    public function __construct()
    {
        $this->publicKey = (string) config('services.webpush.public_key', '');
        $this->privateKey = (string) config('services.webpush.private_key', '');
        $this->subject = (string) config('services.webpush.subject', 'mailto:admin@catatduit.local');
    }

    /**
     * Get the VAPID public key for frontend subscription.
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Lazy initialize WebPush client.
     */
    protected function getWebPush(): ?WebPush
    {
        if ($this->webPush !== null) {
            return $this->webPush;
        }

        if (empty($this->publicKey) || empty($this->privateKey)) {
            Log::warning('[WebPush] VAPID keys are missing in configuration.');

            return null;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => $this->subject,
                    'publicKey' => $this->publicKey,
                    'privateKey' => $this->privateKey,
                ],
            ];

            $this->webPush = new WebPush($auth);
            $this->webPush->setReuseVAPIDHeaders(true);

            return $this->webPush;
        } catch (\Throwable $e) {
            Log::error('[WebPush] Failed to initialize WebPush client: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Send a notification to a specific push subscription.
     *
     * @param  array{title?: string, body?: string, icon?: string, badge?: string, url?: string, data?: array<string, mixed>}  $payload
     */
    public function sendNotification(PushSubscription $subscription, array $payload): bool
    {
        $webPush = $this->getWebPush();
        if (! $webPush) {
            return false;
        }

        try {
            $webPushSub = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
            ]);

            $jsonPayload = json_encode($this->formatPayload($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $report = $webPush->sendOneNotification($webPushSub, $jsonPayload);

            if ($report->isSuccess()) {
                return true;
            }

            Log::warning("[WebPush] Notification failed for subscription #{$subscription->id}: {$report->getReason()}");

            if ($report->isSubscriptionExpired()) {
                Log::info("[WebPush] Removing expired subscription #{$subscription->id}");
                $subscription->delete();
            }

            return false;
        } catch (\Throwable $e) {
            Log::error("[WebPush] Exception sending notification to #{$subscription->id}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Send a notification to multiple subscriptions (optionally filtered by user_id).
     *
     * @param  array{title?: string, body?: string, icon?: string, badge?: string, url?: string, data?: array<string, mixed>}  $payload
     * @return array{sent: int, failed: int, expired: int}
     */
    public function sendToSubscriptions(Collection $subscriptions, array $payload): array
    {
        $webPush = $this->getWebPush();
        $stats = ['sent' => 0, 'failed' => 0, 'expired' => 0];

        if (! $webPush || $subscriptions->isEmpty()) {
            return $stats;
        }

        $jsonPayload = json_encode($this->formatPayload($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $subscriptionMap = [];

        foreach ($subscriptions as $sub) {
            try {
                $webPushSub = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
                ]);

                $webPush->queueNotification($webPushSub, $jsonPayload);
                $subscriptionMap[$sub->endpoint] = $sub;
            } catch (\Throwable $e) {
                Log::warning("[WebPush] Invalid subscription format #{$sub->id}: {$e->getMessage()}");
                $stats['failed']++;
            }
        }

        /** @var MessageSentReport $report */
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();
            $subModel = $subscriptionMap[$endpoint] ?? null;

            if ($report->isSuccess()) {
                $stats['sent']++;
            } else {
                $stats['failed']++;
                Log::warning("[WebPush] Delivery failed for {$endpoint}: {$report->getReason()}");

                if ($report->isSubscriptionExpired()) {
                    $stats['expired']++;
                    if ($subModel) {
                        Log::info("[WebPush] Removing expired subscription #{$subModel->id}");
                        $subModel->delete();
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Normalize notification payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function formatPayload(array $payload): array
    {
        return [
            'title' => $payload['title'] ?? 'Catat Duit 💰',
            'body' => $payload['body'] ?? 'Sudah jam 5 sore nih! Jangan lupa catat pengeluaran hari ini ya.',
            'icon' => $payload['icon'] ?? '/icons/icon-192x192.png',
            'badge' => $payload['badge'] ?? '/icons/icon-192x192.png',
            'data' => [
                'url' => $payload['url'] ?? '/',
                ...($payload['data'] ?? []),
            ],
        ];
    }
}
