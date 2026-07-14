<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google Login belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::query()->updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName() ?: 'Operator',
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'role' => 'Operator',
                    'email_verified_at' => now(),
                ]
            );

            Auth::login($user, remember: true);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('error', 'Autentikasi Google gagal. Periksa kredensial dan callback URL.');
        }
    }

    public function loginAsGuest(Request $request): RedirectResponse
    {
        $guestKey = Str::lower(Str::random(12));

        $user = User::query()->create([
            'name' => 'Guest Operator',
            'email' => "guest-{$guestKey}@synrcypro.local",
            'role' => 'Guest',
            'password' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
