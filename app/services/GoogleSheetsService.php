<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GoogleSheetsService
{
    private const TOKEN_PATH =
        'google-sheets/oauth-token.json';

    /*
    |--------------------------------------------------------------------------
    | Ambil data mentah Monitoring SHE
    |--------------------------------------------------------------------------
    */

    public function getMonitoringSheValues(): array
    {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.she_spreadsheet_id'
            )
        );

        $range = trim(
            (string) config(
                'services.google_sheets.she_range'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID Monitoring SHE belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range Monitoring SHE belum diatur.'
            );
        }

        return $this->getValues(
            $spreadsheetId,
            $range
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil data mentah Monitoring Internal Upload
    |--------------------------------------------------------------------------
    */

    public function getMonitoringInternalUploadValues(): array
    {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.internal_upload_spreadsheet_id'
            )
        );

        $range = trim(
            (string) config(
                'services.google_sheets.internal_upload_range'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID Monitoring Internal Upload belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range Monitoring Internal Upload belum diatur.'
            );
        }

        return $this->getValues(
            $spreadsheetId,
            $range
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil data mentah MASTER_DATABASE
    |--------------------------------------------------------------------------
    */

    public function getMasterDatabaseValues(): array
    {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.master_database_spreadsheet_id'
            )
        );

        $range = trim(
            (string) config(
                'services.google_sheets.master_database_range'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID MASTER_DATABASE belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range MASTER_DATABASE belum diatur.'
            );
        }

        return $this->getValues(
            $spreadsheetId,
            $range
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tambahkan satu baris ke UPDATE_DATA_KARYAWAN
    |--------------------------------------------------------------------------
    */

    public function appendEmployeeDataUpdate(
        array $row
    ): array {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.update_data_spreadsheet_id'
            )
        );

        $range = trim(
            (string) config(
                'services.google_sheets.update_data_range'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID UPDATE_DATA_KARYAWAN belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range UPDATE_DATA_KARYAWAN belum diatur.'
            );
        }

        return $this->appendValues(
            $spreadsheetId,
            $range,
            $row
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tambahkan satu baris ke UPDATE_STATUS_KARYAWAN
    |--------------------------------------------------------------------------
    */

    public function appendEmployeeStatusUpdate(
        array $row
    ): array {
        $spreadsheetId = trim(
            (string) config(
                'services.google_sheets.update_status_spreadsheet_id'
            )
        );

        $range = trim(
            (string) config(
                'services.google_sheets.update_status_range'
            )
        );

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID UPDATE_STATUS_KARYAWAN belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range UPDATE_STATUS_KARYAWAN belum diatur.'
            );
        }

        return $this->appendValues(
            $spreadsheetId,
            $range,
            $row
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Periksa apakah token OAuth Google Sheets sudah tersimpan
    |--------------------------------------------------------------------------
    */

    public function hasStoredToken(): bool
    {
        return Storage::disk('local')->exists(
            self::TOKEN_PATH
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Baca values dari Google Sheets API
    |--------------------------------------------------------------------------
    */

    public function getValues(
        string $spreadsheetId,
        string $range
    ): array {
        $accessToken = $this->getValidAccessToken();

        $response = $this->requestValues(
            $accessToken,
            $spreadsheetId,
            $range
        );

        /*
         * Jika access token sudah tidak valid, refresh dan coba sekali lagi.
         */
        if ($response->status() === 401) {
            $accessToken = $this->getValidAccessToken(
                forceRefresh: true
            );

            $response = $this->requestValues(
                $accessToken,
                $spreadsheetId,
                $range
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                $this->googleErrorMessage(
                    $response,
                    'Gagal membaca Google Spreadsheet.'
                )
            );
        }

        $values = $response->json('values', []);

        return is_array($values)
            ? $values
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Tambahkan values sebagai baris baru Google Sheets
    |--------------------------------------------------------------------------
    */

    public function appendValues(
        string $spreadsheetId,
        string $range,
        array $row
    ): array {
        $spreadsheetId = trim($spreadsheetId);
        $range = trim($range);

        if ($spreadsheetId === '') {
            throw new RuntimeException(
                'Spreadsheet ID tujuan belum diatur.'
            );
        }

        if ($range === '') {
            throw new RuntimeException(
                'Range tujuan belum diatur.'
            );
        }

        $row = array_map(
            static function (mixed $value): mixed {
                return $value === null
                    ? ''
                    : $value;
            },
            array_values($row)
        );

        if ($row === []) {
            throw new RuntimeException(
                'Data yang akan ditulis ke Google Sheets masih kosong.'
            );
        }

        $this->ensureWriteScope();

        $accessToken = $this->getValidAccessToken();

        $response = $this->requestAppendValues(
            $accessToken,
            $spreadsheetId,
            $range,
            $row
        );

        /*
         * Jika access token sudah tidak valid, refresh dan coba sekali lagi.
         */
        if ($response->status() === 401) {
            $accessToken = $this->getValidAccessToken(
                forceRefresh: true
            );

            $response = $this->requestAppendValues(
                $accessToken,
                $spreadsheetId,
                $range,
                $row
            );
        }

        if ($response->failed()) {
            throw new RuntimeException(
                $this->googleErrorMessage(
                    $response,
                    'Gagal menambahkan data ke Google Spreadsheet.'
                )
            );
        }

        $result = $response->json();

        if (!is_array($result)) {
            throw new RuntimeException(
                'Respons penulisan Google Spreadsheet tidak valid.'
            );
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Kirim request ke Google Sheets API
    |--------------------------------------------------------------------------
    */

    private function requestValues(
        string $accessToken,
        string $spreadsheetId,
        string $range
    ): Response {
       $range = trim($range);

// hapus quote pembungkus jika ada
        $range = trim($range, "'");

        $url =
       'https://sheets.googleapis.com/v4/spreadsheets/' .
         rawurlencode($spreadsheetId) .
        '/values/' .
         rawurlencode($range);

        return Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->get(
                $url,
                [
                    'majorDimension' => 'ROWS',
                    'valueRenderOption' =>
                        'FORMATTED_VALUE',
                    'dateTimeRenderOption' =>
                        'FORMATTED_STRING',
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Kirim request append ke Google Sheets API
    |--------------------------------------------------------------------------
    */

    private function requestAppendValues(
        string $accessToken,
        string $spreadsheetId,
        string $range,
        array $row
    ): Response {
        $query = http_build_query(
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
                'includeValuesInResponse' => 'true',
            ],
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $url =
            'https://sheets.googleapis.com/v4/spreadsheets/' .
            rawurlencode($spreadsheetId) .
            '/values/' .
            rawurlencode($range) .
            ':append?' .
            $query;

        return Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post(
                $url,
                [
                    'range' => $range,
                    'majorDimension' => 'ROWS',
                    'values' => [$row],
                ]
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Pastikan token memiliki izin tulis Google Sheets
    |--------------------------------------------------------------------------
    */

    private function ensureWriteScope(): void
    {
        $token = $this->readStoredToken();

        $scopeText = trim(
            (string) Arr::get(
                $token,
                'scope',
                ''
            )
        );

        $scopes = preg_split(
            '/\\s+/',
            $scopeText,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        if (
            !in_array(
                'https://www.googleapis.com/auth/spreadsheets',
                $scopes,
                true
            )
        ) {
            throw new RuntimeException(
                'Token Google Sheets belum memiliki izin tulis. ' .
                'Hubungkan ulang OAuth menggunakan scope spreadsheets.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil access token yang masih aktif
    |--------------------------------------------------------------------------
    */

    private function getValidAccessToken(
        bool $forceRefresh = false
    ): string {
        $token = $this->readStoredToken();

        $accessToken = trim(
            (string) Arr::get(
                $token,
                'access_token',
                ''
            )
        );

        $expiresAt = (int) Arr::get(
            $token,
            'expires_at',
            0
        );

        $stillValid =
            $accessToken !== '' &&
            $expiresAt >
                now()
                    ->addMinute()
                    ->timestamp;

        if (!$forceRefresh && $stillValid) {
            return $accessToken;
        }

        return $this->refreshAccessToken($token);
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh access token
    |--------------------------------------------------------------------------
    */

    private function refreshAccessToken(
        array $storedToken
    ): string {
        $refreshToken = trim(
            (string) Arr::get(
                $storedToken,
                'refresh_token',
                ''
            )
        );

        if ($refreshToken === '') {
            throw new RuntimeException(
                'Refresh token Google Sheets tidak ditemukan. ' .
                'Hubungkan ulang OAuth Google Sheets.'
            );
        }

        $clientId = trim(
            (string) config(
                'services.google_sheets.client_id'
            )
        );

        $clientSecret = trim(
            (string) config(
                'services.google_sheets.client_secret'
            )
        );

        if (
            $clientId === '' ||
            $clientSecret === ''
        ) {
            throw new RuntimeException(
                'Client ID atau Client Secret Google Sheets belum lengkap.'
            );
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post(
                'https://oauth2.googleapis.com/token',
                [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                $this->googleErrorMessage(
                    $response,
                    'Gagal memperbarui access token Google.'
                )
            );
        }

        $newAccessToken = trim(
            (string) $response->json(
                'access_token',
                ''
            )
        );

        if ($newAccessToken === '') {
            throw new RuntimeException(
                'Access token baru tidak ditemukan.'
            );
        }

        $expiresIn = (int) $response->json(
            'expires_in',
            3600
        );

        $updatedToken = array_merge(
            $storedToken,
            [
                'access_token' =>
                    $newAccessToken,

                'token_type' =>
                    (string) $response->json(
                        'token_type',
                        'Bearer'
                    ),

                'scope' =>
                    (string) $response->json(
                        'scope',
                        Arr::get(
                            $storedToken,
                            'scope',
                            ''
                        )
                    ),

                'expires_in' =>
                    $expiresIn,

                'expires_at' =>
                    now()
                        ->addSeconds($expiresIn)
                        ->timestamp,

                'refreshed_at' =>
                    now()->toIso8601String(),
            ]
        );

        $this->storeToken($updatedToken);

        return $newAccessToken;
    }

    /*
    |--------------------------------------------------------------------------
    | Baca token dari storage lokal
    |--------------------------------------------------------------------------
    */

    private function readStoredToken(): array
    {
        if (
            !Storage::disk('local')->exists(
                self::TOKEN_PATH
            )
        ) {
            throw new RuntimeException(
                'Token Google Sheets belum tersedia. ' .
                'Buka /google/oauth/redirect terlebih dahulu.'
            );
        }

        $token = json_decode(
            (string) Storage::disk('local')->get(
                self::TOKEN_PATH
            ),
            true
        );

        if (!is_array($token)) {
            throw new RuntimeException(
                'Format token Google Sheets tidak valid.'
            );
        }

        return $token;
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan token hasil refresh
    |--------------------------------------------------------------------------
    */

    private function storeToken(array $token): void
    {
        Storage::disk('local')->put(
            self::TOKEN_PATH,
            json_encode(
                $token,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil pesan error dari respons Google
    |--------------------------------------------------------------------------
    */

    private function googleErrorMessage(
        Response $response,
        string $fallback
    ): string {
        $message = $response->json(
            'error.message'
        );

        if (
            is_string($message) &&
            $message !== ''
        ) {
            return $message;
        }

        $description = $response->json(
            'error_description'
        );

        if (
            is_string($description) &&
            $description !== ''
        ) {
            return $description;
        }

        return $fallback;
    }
}