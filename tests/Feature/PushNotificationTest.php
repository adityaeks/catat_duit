<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_vapid_public_key(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('push.key'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['publicKey']);
    }

    public function test_user_can_subscribe_to_push_notifications(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-subscription-endpoint-123';

        $payload = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcUbVUtu_...fake',
                'auth' => 'tBHItJI5svbpez7KI4CCXg==',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $response = $this->actingAs($user)->postJson(route('push.subscribe'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', $endpoint),
            'content_encoding' => 'aes128gcm',
        ]);
    }

    public function test_subscribing_again_with_same_endpoint_updates_existing_record(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-subscription-endpoint-123';

        $payload = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'first-key',
                'auth' => 'first-auth',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $this->actingAs($user)->postJson(route('push.subscribe'), $payload);

        $updatedPayload = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'updated-key',
                'auth' => 'updated-auth',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $this->actingAs($user)->postJson(route('push.subscribe'), $updatedPayload);

        $this->assertDatabaseCount('push_subscriptions', 1);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'public_key' => 'updated-key',
            'auth_token' => 'updated-auth',
        ]);
    }

    public function test_user_can_unsubscribe(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-endpoint-to-remove';

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', $endpoint),
            'endpoint' => $endpoint,
            'public_key' => 'key',
            'auth_token' => 'auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->assertDatabaseCount('push_subscriptions', 1);

        $response = $this->actingAs($user)->postJson(route('push.unsubscribe'), [
            'endpoint' => $endpoint,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseCount('push_subscriptions', 0);
    }

    public function test_send_test_push_with_no_subscriptions_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('push.test'));

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_send_test_push_with_mocked_web_push_service(): void
    {
        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-endpoint-test';

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', $endpoint),
            'endpoint' => $endpoint,
            'public_key' => 'key',
            'auth_token' => 'auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $mockWebPush = Mockery::mock(WebPushService::class);
        $mockWebPush->shouldReceive('sendToSubscriptions')
            ->once()
            ->andReturn(['sent' => 1, 'failed' => 0, 'expired' => 0]);

        $this->app->instance(WebPushService::class, $mockWebPush);

        $response = $this->actingAs($user)->postJson(route('push.test'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_daily_reminder_command_runs_successfully(): void
    {
        $this->artisan('reminder:daily')
            ->expectsOutput('No active push subscriptions found.')
            ->assertSuccessful();

        $user = User::factory()->create();
        $endpoint = 'https://fcm.googleapis.com/fcm/send/fake-endpoint-cmd';

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint_hash' => hash('sha256', $endpoint),
            'endpoint' => $endpoint,
            'public_key' => 'key',
            'auth_token' => 'auth',
            'content_encoding' => 'aes128gcm',
        ]);

        $mockWebPush = Mockery::mock(WebPushService::class);
        $mockWebPush->shouldReceive('sendToSubscriptions')
            ->once()
            ->andReturn(['sent' => 1, 'failed' => 0, 'expired' => 0]);

        $this->app->instance(WebPushService::class, $mockWebPush);

        $this->artisan('reminder:daily')
            ->expectsOutput('Sending daily reminder to 1 registered device(s)...')
            ->expectsOutput('Completed: 1 sent, 0 failed, 0 expired/cleaned.')
            ->assertSuccessful();
    }
}
