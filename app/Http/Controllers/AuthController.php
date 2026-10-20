<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Always pass true to remember the user indefinitely
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $user->createDefaultCategories();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak sesuai.',
        ])->onlyInput('email');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'Google OAuth belum dikonfigurasi. Harap isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Gagal masuk dengan Google. Silakan coba kembali.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'name' => $user->name ?: ($googleUser->getName() ?? 'Pengguna Catat Duit'),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?? 'Pengguna Catat Duit',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
            ]);
        }

        // Auto-seed default categories for this user if they don't have any yet
        $user->createDefaultCategories();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang, '.$user->name.'! Berhasil masuk dengan Akun Google.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
