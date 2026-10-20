<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Catat Duit');
        $response->assertSee('Masuk');
        $response->assertSee('Masuk dengan Google');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@catatduit.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'admin@catatduit.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_google_redirect_warns_when_credentials_not_set(): void
    {
        Config::set('services.google.client_id', null);
        Config::set('services.google.client_secret', null);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_google_oauth_callback_creates_user_and_default_categories(): void
    {
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-unique-id-999');
        $googleUser->shouldReceive('getName')->andReturn('Budi Santoso');
        $googleUser->shouldReceive('getEmail')->andReturn('budi@example.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar-budi.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-unique-id-999', $user->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar-budi.jpg', $user->avatar);
        $this->assertAuthenticatedAs($user);

        // Verify default categories were seeded for this user
        $this->assertEquals(14, $user->categories()->count());
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
        ]);
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Gaji Utama',
        ]);
    }

    public function test_google_oauth_callback_updates_existing_user(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'google_id' => null,
            'avatar' => null,
        ]);

        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-existing-456');
        $googleUser->shouldReceive('getName')->andReturn('Existing User Updated');
        $googleUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar-existing.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($existing);

        $existing->refresh();
        $this->assertEquals('google-existing-456', $existing->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/avatar-existing.jpg', $existing->avatar);
    }
}
