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
    private const TOKEN_PATH = 'google-sheets/oauth-token.json';

    private const STATE_SESSION_KEY = 'google_sheets_oauth_state';

    private const RETURN_URL_SESSION_KEY =
        'google_sheets_oauth_return_url';

    /**
     * Mengarahkan pengguna ke halaman persetujuan Google.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = trim(
            (string) config('services.google_sheets.client_id')
        );

        $redirectUri = trim(
            (string) config('services.google_sheets.redirect_uri')
        );

        $allowedEmail = strtolower(
            trim(
                (string) config(
                    'services.google_sheets.allowed_email'
                )
            )
        );

        if ($clientId === '' || $redirectUri === '') {
            return redirect()
                ->route('database.employees')
                ->with(
                    'error',
                    'Konfigurasi OAuth Google Sheets belum lengkap.'
                );
        }

        $state = Str::random(64);

        $request->session()->put(
            self::STATE_SESSION_KEY,
            $state
        );

        $returnUrl = $this->resolveReturnUrl();

        $request->session()->put(
            self::RETURN_URL_SESSION_KEY,
            $returnUrl
        );

        $query = http_build_query(
            [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => implode(
                    ' ',
                    [
                        'openid',
                        'email',
                        'https://www.googleapis.com/auth/spreadsheets.readonly',
                    ]
                ),
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
            'https://accounts.google.com/o/oauth2/v2/auth?' .
            $query
        );
    }

    /**
     * Menerima callback Google, menukar authorization code menjadi
     * token, memeriksa akun, lalu menyimpan token secara lokal.
     */
    public function callback(Request $request): RedirectResponse
    {
        $returnUrl = (string) $request->session()->pull(
            self::RETURN_URL_SESSION_KEY,
            route('database.employees')
        );

        if ($request->filled('error')) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Izin Google dibatalkan: ' .
                    $request->string('error')->toString()
                );
        }

        $expectedState = (string) $request->session()->pull(
            self::STATE_SESSION_KEY,
            ''
        );

        $receivedState = (string) $request->query(
            'state',
            ''
        );

        if (
            $expectedState === '' ||
            $receivedState === '' ||
            ! hash_equals($expectedState, $receivedState)
        ) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'State OAuth Google tidak valid. ' .
                    'Ulangi proses koneksi.'
                );
        }

        $authorizationCode = (string) $request->query(
            'code',
            ''
        );

        if ($authorizationCode === '') {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Authorization code Google tidak ditemukan.'
                );
        }

        $clientId = trim(
            (string) config('services.google_sheets.client_id')
        );

        $clientSecret = trim(
            (string) config('services.google_sheets.client_secret')
        );

        $redirectUri = trim(
            (string) config('services.google_sheets.redirect_uri')
        );

        if (
            $clientId === '' ||
            $clientSecret === '' ||
            $redirectUri === ''
        ) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Konfigurasi OAuth Google Sheets belum lengkap.'
                );
        }

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(30)
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
                ->to($returnUrl)
                ->with(
                    'error',
                    $this->googleErrorMessage(
                        $tokenResponse,
                        'Gagal mendapatkan token Google.'
                    )
                );
        }

        $token = $tokenResponse->json();

        if (! is_array($token)) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Respons token Google tidak valid.'
                );
        }

        $accessToken = trim(
            (string) Arr::get(
                $token,
                'access_token',
                ''
            )
        );

        if ($accessToken === '') {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Access token Google tidak ditemukan.'
                );
        }

        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->get(
                'https://openidconnect.googleapis.com/v1/userinfo'
            );

        if ($userResponse->failed()) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    $this->googleErrorMessage(
                        $userResponse,
                        'Gagal memeriksa akun Google yang digunakan.'
                    )
                );
        }

        $googleEmail = strtolower(
            trim(
                (string) $userResponse->json(
                    'email',
                    ''
                )
            )
        );

        if ($googleEmail === '') {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Email akun Google tidak ditemukan.'
                );
        }

$allowedEmails = array_filter(
    array_map(
        function ($email) {
            return strtolower(trim($email));
        },
        explode(
            ',',
            (string) config(
                'services.google_sheets.allowed_email'
            )
        )
    )
);

dd(
    $googleEmail,
    $allowedEmails
);

if (
    ! in_array(
        $googleEmail,
        $allowedEmails,
        true
    )
) {

    return redirect()
        ->to($returnUrl)
        ->with(
            'error',
            'Email Google Anda belum terdaftar di SYNRGYPRO.'
        );
}

        $oldToken = [];

        if (
            Storage::disk('local')->exists(
                self::TOKEN_PATH
            )
        ) {
            $oldTokenJson = Storage::disk('local')->get(
                self::TOKEN_PATH
            );

            $oldToken = json_decode(
                (string) $oldTokenJson,
                true
            ) ?: [];
        }

        $refreshToken = trim(
            (string) (
                Arr::get(
                    $token,
                    'refresh_token'
                ) ??
                Arr::get(
                    $oldToken,
                    'refresh_token',
                    ''
                )
            )
        );

        if ($refreshToken === '') {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Google tidak mengirim refresh token. ' .
                    'Cabut akses SYNRGYPRO dari akun Google, ' .
                    'lalu hubungkan ulang.'
                );
        }

        $expiresIn = max(
            60,
            (int) Arr::get(
                $token,
                'expires_in',
                3600
            )
        );

        $tokenData = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => (string) Arr::get(
                $token,
                'token_type',
                'Bearer'
            ),
            'scope' => (string) Arr::get(
                $token,
                'scope',
                ''
            ),
            'expires_in' => $expiresIn,
            'expires_at' => now()
                ->addSeconds($expiresIn)
                ->timestamp,
            'email' => $googleEmail,
            'connected_at' => now()->toIso8601String(),
        ];

        $saved = Storage::disk('local')->put(
            self::TOKEN_PATH,
            json_encode(
                $tokenData,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES |
                JSON_THROW_ON_ERROR
            )
        );

        if (! $saved) {
            return redirect()
                ->to($returnUrl)
                ->with(
                    'error',
                    'Token Google berhasil diperoleh, ' .
                    'tetapi gagal disimpan ke storage Laravel.'
                );
        }

        return redirect()
            ->to($returnUrl)
            ->with(
                'success',
                'Google Sheets berhasil dihubungkan menggunakan ' .
                $googleEmail .
                '.'
            );
    }

    /**
     * Mengambil pesan error yang dikirim Google.
     */
    private function googleErrorMessage(
        Response $response,
        string $fallback
    ): string {
        $description = $response->json(
            'error_description'
        );

        if (
            is_string($description) &&
            trim($description) !== ''
        ) {
            return trim($description);
        }

        $message = $response->json(
            'error.message'
        );

        if (
            is_string($message) &&
            trim($message) !== ''
        ) {
            return trim($message);
        }

        $error = $response->json('error');

        if (
            is_string($error) &&
            trim($error) !== ''
        ) {
            return trim($error);
        }

        return $fallback .
            ' HTTP ' .
            $response->status() .
            '.';
    }

    /**
     * Menentukan halaman tujuan setelah proses OAuth selesai.
     */
    private function resolveReturnUrl(): string
    {
        $fallbackUrl = route('database.employees');
        $previousUrl = url()->previous();
        $appUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        if (
            $previousUrl !== '' &&
            $appUrl !== '' &&
            str_starts_with(
                $previousUrl,
                $appUrl
            )
        ) {
            return $previousUrl;
        }

        return $fallbackUrl;
    }
}