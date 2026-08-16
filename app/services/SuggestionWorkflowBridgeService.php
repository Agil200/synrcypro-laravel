<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class SuggestionWorkflowBridgeService
{
    private const STAGE_GL_QCC = 'GL_QCC';
    private const STAGE_SH = 'SH';
    private const STAGE_DH_PM = 'DH_PM';

    private const ALLOWED_DECISIONS = [
        self::STAGE_GL_QCC => [
            'VERIFIED',
            'REVISION',
            'REJECTED',
        ],
        self::STAGE_SH => [
            'APPROVED',
            'REJECTED',
        ],
        self::STAGE_DH_PM => [
            'APPROVED',
            'REJECTED',
        ],
    ];

    public function ping(string $email): array
    {
        $email = $this->normalizeEmail($email);

        if ($email === '') {
            throw new RuntimeException(
                'Email login tidak ditemukan.'
            );
        }

        return $this->send([
            'op' => 'ping',
            'email' => $email,
        ]);
    }

    public function updateGl(
        string $email,
        string $noSs,
        string $decision,
        ?string $note = null
    ): array {
        return $this->updateWorkflow(
            $email,
            $noSs,
            self::STAGE_GL_QCC,
            $decision,
            $note
        );
    }

    public function updateSh(
        string $email,
        string $noSs,
        string $decision,
        ?string $note = null
    ): array {
        return $this->updateWorkflow(
            $email,
            $noSs,
            self::STAGE_SH,
            $decision,
            $note
        );
    }

    public function updateDhPm(
        string $email,
        string $noSs,
        string $decision,
        ?string $note = null
    ): array {
        return $this->updateWorkflow(
            $email,
            $noSs,
            self::STAGE_DH_PM,
            $decision,
            $note
        );
    }

    private function updateWorkflow(
        string $email,
        string $noSs,
        string $stage,
        string $decision,
        ?string $note = null
    ): array {
        $email = $this->normalizeEmail($email);
        $noSs = trim($noSs);
        $stage = strtoupper(trim($stage));
        $decision = strtoupper(trim($decision));
        $note = trim((string) $note);

        if ($email === '') {
            throw new RuntimeException(
                'Email login tidak ditemukan.'
            );
        }

        if ($noSs === '') {
            throw new RuntimeException(
                'NO SS wajib diisi.'
            );
        }

        if (!array_key_exists($stage, self::ALLOWED_DECISIONS)) {
            throw new RuntimeException(
                'Stage workflow Laravel tidak valid.'
            );
        }

        if (
            !in_array(
                $decision,
                self::ALLOWED_DECISIONS[$stage],
                true
            )
        ) {
            throw new RuntimeException(
                'Decision '.$stage.' tidak valid.'
            );
        }

        if (
            in_array(
                $decision,
                ['REVISION', 'REJECTED'],
                true
            )
            && mb_strlen($note) < 5
        ) {
            throw new RuntimeException(
                'Catatan / alasan minimal 5 karakter '
                .'untuk REVISI atau REJECT.'
            );
        }

        return $this->send([
            'op' => 'workflow',
            'email' => $email,
            'noSS' => $noSs,
            'stage' => $stage,
            'decision' => $decision,
            'note' => $note,
        ]);
    }

    private function send(array $payload): array
    {
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

        try {
            $json = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Payload workflow gagal dibuat.',
                previous: $e
            );
        }

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
            ->connectTimeout(
                max(
                    3,
                    (int) config(
                        'suggestion_bridge.connect_timeout',
                        10
                    )
                )
            )
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

        $body = trim((string) $response->body());

        if ($body === '') {
            throw new RuntimeException(
                'Apps Script Bridge mengembalikan respons kosong.'
            );
        }

        try {
            $data = json_decode(
                $body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Respons Apps Script bukan JSON valid.',
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                'Respons Apps Script bukan object JSON yang valid.'
            );
        }

        return $data;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}