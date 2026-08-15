<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Mengarahkan pengguna ke halaman login Google.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        if (
            ! config('services.google.client_id')
            || ! config('services.google.client_secret')
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Google Login belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.'
                );
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback Google Login.
     */
    public function handleGoogleCallback(
        Request $request
    ): RedirectResponse {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = Str::lower(
                trim((string) $googleUser->getEmail())
            );

            $existingUser = $email !== ''
                ? User::query()->where('email', $email)->first()
                : null;

            $allowedEmails = config('access.login_allowed_emails', []);

            /*
             * Email dapat login apabila sudah didaftarkan di User Management
             * atau masih berada pada allowlist legacy di .env.
             */
            $isAllowed =
                $email !== ''
                && (
                    $existingUser !== null
                    || in_array($email, $allowedEmails, true)
                );

            if (! $isAllowed) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Email Anda belum terdaftar sebagai pengguna SYNRGYPRO. Silakan menghubungi Administrator.'
                    );
            }

            if ($existingUser && ! $existingUser->isActive()) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Akun SYNRGYPRO Anda sedang nonaktif. Hubungi Administrator.'
                    );
            }

            $user = $existingUser ?: new User([
                'email' => $email,
            ]);

            $isNewUser = ! $user->exists;

            $user->name =
                $googleUser->getName()
                ?: $user->name
                ?: 'Pengguna SYNRGYPRO';

            $user->google_id = $googleUser->getId();
            $user->avatar = $googleUser->getAvatar();
            $user->email_verified_at = now();

            /*
             * Kolom legacy role tetap dipertahankan untuk module lama.
             * Role database baru hanya diberikan otomatis pada user baru.
             */
            if ($isNewUser || empty($user->role)) {
                $user->role = 'Operator';
            }

            if ($isNewUser && empty($user->role_id)) {
                $user->role_id = Role::query()
                    ->where('slug', 'operator')
                    ->value('id');
            }

            if ($isNewUser) {
                $user->is_active = true;
            }

            if ($isNewUser && empty($user->password)) {
                $user->password = Hash::make(
                    Str::random(40)
                );
            }

            $user->last_login_at = now();
            $user->save();

            Auth::login(
                $user,
                remember: true
            );

            $request->session()->regenerate();

            $request->session()->put(
                'access_mode',
                'member'
            );

            return redirect()->intended(
                route('dashboard')
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Login Google gagal. Periksa konfigurasi OAuth Google.'
                );
        }
    }

    /**
     * Login sebagai Guest dengan akses read-only.
     */
    public function loginAsGuest(
        Request $request
    ): RedirectResponse {
        $user = User::query()->firstOrCreate(
            [
                'email' => 'guest@synrgypro.local',
            ],
            [
                'name' => 'Guest Operator',
                'role' => 'Guest',
                'password' => Hash::make(
                    Str::random(40)
                ),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->forceFill([
            'name' => 'Guest Operator',
            'role' => 'Guest',
            'is_active' => true,
        ])->save();

        Auth::login($user);

        $request->session()->regenerate();

        $request->session()->put(
            'access_mode',
            'guest'
        );

        return redirect()->route('dashboard');
    }

    /**
     * Logout pengguna.
     */
    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
