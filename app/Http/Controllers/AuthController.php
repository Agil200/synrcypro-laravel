<?php

namespace App\Http\Controllers;

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
            ! config('services.google.client_id') ||
            ! config('services.google.client_secret')
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

            $allowedEmails = config(
                'access.allowed_emails',
                []
            );

            /*
             * Email kosong atau tidak terdapat dalam daftar akses.
             */
            if (
                $email === '' ||
                ! in_array($email, $allowedEmails, true)
            ) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Email Anda tidak terdaftar sebagai pengguna SYNRGYPRO. Silakan menghubungi call center Manpower.'
                    );
            }

            /*
             * Cari akun berdasarkan email.
             *
             * Menggunakan firstOrNew agar role pengguna lama
             * tidak selalu ditimpa menjadi Operator.
             */
            $user = User::query()->firstOrNew([
                'email' => $email,
            ]);

            $isNewUser = ! $user->exists;

            $user->name =
                $googleUser->getName()
                ?: $user->name
                ?: 'Pengguna SYNRGYPRO';

            $user->google_id =
                $googleUser->getId();

            $user->avatar =
                $googleUser->getAvatar();

            $user->email_verified_at = now();

            /*
             * Role Operator hanya diberikan saat akun baru dibuat
             * atau jika role sebelumnya masih kosong.
             */
            if ($isNewUser || empty($user->role)) {
                $user->role = 'Operator';
            }

            /*
             * Password acak hanya digunakan agar kolom password
             * tetap aman apabila tidak nullable.
             */
            if ($isNewUser && empty($user->password)) {
                $user->password = Hash::make(
                    Str::random(40)
                );
            }

            $user->save();

            Auth::login(
                $user,
                remember: true
            );

            $request->session()->regenerate();

            /*
             * Menandai bahwa pengguna login melalui
             * akun Google yang terdaftar.
             */
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
        /*
         * Menggunakan satu akun Guest bersama agar database
         * tidak membuat akun Guest baru pada setiap login.
         */
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
            ]
        );

        /*
         * Memastikan akun tersebut selalu menjadi Guest.
         */
        $user->forceFill([
            'name' => 'Guest Operator',
            'role' => 'Guest',
        ])->save();

        Auth::login($user);

        $request->session()->regenerate();

        /*
         * Penanda untuk membatasi tombol CRUD.
         */
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