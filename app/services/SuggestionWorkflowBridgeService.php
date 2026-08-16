<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SuggestionWorkflowBridgeService
{
    public function ping(
        string $email
    ): array {
        return $this->send([
            'op' => 'ping',
            'email' => $this->normalizeEmail($email),
        ]);
    }

    public function updateGl(
        string $email,
        string $noSs,
        string $decision,
        ?string $note = null
    ): array {
        return $this->send([
            'op' => 'workflow',
            'email' => $this->normalizeEmail($email),
            'noSS' => trim($noSs),
            'stage' => 'GL_QCC',
            'decision' => strtoupper(trim($decision)),
            'note' => trim((string) $note),
        ]);
    }


    public function updateSh(
        string $email,
        string $noSs,
        string $decision,
        ?string $note = null
    ): array {
        return $this->send([
            'op' => 'workflow',
            'email' => $this->normalizeEmail($email),
            'noSS' => trim($noSs),
            'stage' => 'SH',
            'decision' => strtoupper(trim($decision)),
            'note' => trim((string) $note),
        ]);
    }

    private function send(
        array $payload
    ): array {
        $url = trim(
            (string) config(
                'suggestion_bridge.url',
                ''
            )
        );

        $secret = trim(
            (string) config(
                'suggestion_bridge.secret',
                ''
            )
        );

        if ($url === '') {
            throw new RuntimeException(
                'SUGGESTION_APPS_SCRIPT_URL belum diisi.'
            );
        }

        if (strlen($secret) < 32) {
            throw new RuntimeException(
                'SUGGESTION_APPS_SCRIPT_SECRET belum diisi '
                .'atau terlalu pendek.'
            );
        }

        $payload['v'] = 1;
        $payload['iat'] = now()->timestamp;
        $payload['nonce'] = (string) Str::uuid();

        /*
         * JSON key order di sini sengaja tidak perlu disamakan
         * dengan Apps Script karena signature dihitung terhadap
         * payload Base64URL yang dikirim apa adanya.
         */
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        $encoded = rtrim(
            strtr(
                base64_encode($json),
                '+/',
                '-_'
            ),
            '='
        );

        $signature = hash_hmac(
            'sha256',
            $encoded,
            $secret
        );

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(
                max(
                    5,
                    (int) config(
                        'suggestion_bridge.timeout',
                        20
                    )
                )
            )
            ->withOptions([
                'allow_redirects' => true,
            ])
            ->post(
                $url,
                [
                    'action' => 'laravel_bridge',
                    'payload' => $encoded,
                    'signature' => $signature,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Apps Script Bridge HTTP '
                .$response->status().'.'
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new RuntimeException(
                'Respons Apps Script bukan JSON valid.'
            );
        }

        return $data;
    }

    private function normalizeEmail(
        string $email
    ): string {
        return strtolower(
            trim($email)
        );
    }
}