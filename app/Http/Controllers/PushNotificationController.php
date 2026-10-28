<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushNotificationController extends Controller
{
    public function __construct(
        public WebPushService $webPushService
    ) {}

    /**
     * Get the VAPID public key.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => $this->webPushService->getPublicKey(),
        ]);
    }

    /**
     * Register or update a Web Push subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $endpoint = $validated['endpoint'];
        $endpointHash = hash('sha256', $endpoint);

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => Auth::id(),
                'endpoint' => $endpoint,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengingat harian berhasil diaktifkan di perangkat ini.',
            'id' => $subscription->id,
        ]);
    }

    /**
     * Remove a Web Push subscription.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = (string) $request->input('endpoint');
        if (! empty($endpoint)) {
            PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
        } elseif (Auth::check()) {
            PushSubscription::where('user_id', Auth::id())->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengingat harian dinonaktifkan.',
        ]);
    }

    /**
     * Send an immediate test push notification to user's registered device(s).
     */
    public function sendTest(Request $request): JsonResponse
    {
        $endpoint = (string) $request->input('endpoint');
        $query = PushSubscription::query();

        if (! empty($endpoint)) {
            $query->where('endpoint_hash', hash('sha256', $endpoint));
        } elseif (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat belum terdaftar untuk menerima push notification. Pastikan toggle pengingat sudah aktif.',
            ], 422);
        }

        $payload = [
            'title' => 'Catat Duit 💰',
            'body' => 'Tes berhasil! Ini contoh pengingat sore yang dikirim langsung dari server, tetap muncul walau web ditutup.',
            'icon' => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-192x192.png',
            'data' => [
                'url' => route('dashboard'),
                'type' => 'test-reminder',
            ],
        ];

        $stats = $this->webPushService->sendToSubscriptions($subscriptions, $payload);

        if ($stats['sent'] > 0) {
            return response()->json([
                'success' => true,
                'message' => "Notifikasi uji coba berhasil dikirim ke {$stats['sent']} perangkat!",
                'stats' => $stats,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim notifikasi uji coba ke perangkat. Pastikan izin notifikasi aktif di browser.',
            'stats' => $stats,
        ], 500);
    }
}
