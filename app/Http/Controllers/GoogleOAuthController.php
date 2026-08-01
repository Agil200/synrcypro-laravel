<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleOAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = (string) config('services.google_sheets.client_id');
        $redirectUri = (string) config('services.google_sheets.redirect_uri');
        $allowedEmail = (string) config('services.google_sheets.allowed_email');

        if ($clientId === '' || $redirectUri === '') {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with('error', 'Konfigurasi OAuth Google Sheets belum lengkap.');
        }

        $state = Str::random(64);

        $request->session()->put(
            'google_sheets_oauth_state',
            $state
        );

        $query = http_build_query(
            [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => implode(' ', [
                    'openid',
                    'email',
                    'https://www.googleapis.com/auth/spreadsheets.readonly',
                ]),
                'access_type' => 'offline',
                'include_granted_scopes' => 'true',
                'prompt' => 'consent select_account',
                'login_hint' => $allowedEmail,
                'state' => $state,
            ],
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        return redirect()->away(
            'https://accounts.google.com/o/oauth2/v2/auth?' . $query
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Izin Google dibatalkan: ' .
                    $request->string('error')->toString()
                );
        }

        $expectedState = (string) $request->session()->pull(
            'google_sheets_oauth_state',
            ''
        );

        $receivedState = (string) $request->query('state', '');

        if (
            $expectedState === '' ||
            $receivedState === '' ||
            ! hash_equals($expectedState, $receivedState)
        ) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'State OAuth Google tidak valid. Ulangi proses koneksi.'
                );
        }

        $authorizationCode = (string) $request->query('code', '');

        if ($authorizationCode === '') {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Authorization code Google tidak ditemukan.'
                );
        }

        $clientId = (string) config('services.google_sheets.client_id');
        $clientSecret = (string) config('services.google_sheets.client_secret');
        $redirectUri = (string) config('services.google_sheets.redirect_uri');

        if (
            $clientId === '' ||
            $clientSecret === '' ||
            $redirectUri === ''
        ) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Konfigurasi OAuth Google Sheets belum lengkap.'
                );
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->post(
                'https://oauth2.googleapis.com/token',
                [
                    'code' => $authorizationCode,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ]
            );

        if ($tokenResponse->failed()) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    $this->googleErrorMessage(
                        $tokenResponse,
                        'Gagal mendapatkan token Google.'
                    )
                );
        }

        $token = $tokenResponse->json();
        $accessToken = (string) Arr::get($token, 'access_token', '');

        if ($accessToken === '') {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Access token Google tidak ditemukan.'
                );
        }

        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://openidconnect.googleapis.com/v1/userinfo');

        if ($userResponse->failed()) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Gagal memeriksa akun Google yang digunakan.'
                );
        }

        $googleEmail = strtolower(
            trim((string) $userResponse->json('email', ''))
        );

        $allowedEmail = strtolower(
            trim((string) config('services.google_sheets.allowed_email'))
        );

        if (
            $allowedEmail !== '' &&
            $googleEmail !== $allowedEmail
        ) {
            return redirect()
                ->route('mine-permit.monitoring-she')
                ->with(
                    'error',
                    'Gunakan akun Google ' .
                    $allowedEmail .
                    ' untuk menghubungkan Spreadsheet.'
                );
        }

        $tokenPath = 'google-sheets/oauth-token.json';
        $oldToken = [];

        if (Storage::disk('local')->exists($tokenPath)) {
            $oldToken = json_decode(
                (string) Storage::disk('local')->get($tokenPath),
                true
            ) ?: [];
        }

        $refreshToken = (string) (
            Arr::get($token, 'refresh_token') ??
            Arr::get($oldToken, 'refresh_token', '')
        );

        $expiresIn = (int) Arr::get($token, 'expires_in', 3600);

        $tokenData = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => (string) Arr::get(
                $token,
                'token_type',
                'Bearer'
            ),
            'scope' => (string) Arr::get($token, 'scope', ''),
            'expires_in' => $expiresIn,
            'expires_at' => now()
                ->addSeconds($expiresIn)
                ->timestamp,
            'email' => $googleEmail,
            'connected_at' => now()->toIso8601String(),
        ];

        Storage::disk('local')->put(
            $tokenPath,
            json_encode(
                $tokenData,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );

        return redirect()
            ->route('mine-permit.monitoring-she')
            ->with(
                'success',
                'Google Sheets berhasil dihubungkan menggunakan ' .
                $googleEmail .
                '.'
            );
    }

    private function googleErrorMessage(
        Response $response,
        string $fallback
    ): string {
        $description = $response->json('error_description');

        if (is_string($description) && $description !== '') {
            return $description;
        }

        $message = $response->json('error.message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return $fallback;
    }
}