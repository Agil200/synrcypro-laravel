<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function ask(string $message): string
    {
        $apiKey = trim((string) config('services.gemini.key'));
        $model = trim((string) config('services.gemini.model', 'gemini-3.6-flash'));
        $url = rtrim(
            trim((string) config(
                'services.gemini.url',
                'https://generativelanguage.googleapis.com/v1beta/interactions'
            )),
            '/'
        );

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $systemInstruction = <<<'PROMPT'
Anda adalah SYNRGY Assistant, asisten AI internal aplikasi SYNRGYPRO.

Gunakan Bahasa Indonesia yang jelas, profesional, ringkas, dan mudah dipahami.

Anda membantu pengguna mengenai:
- penggunaan aplikasi SYNRGYPRO
- APD
- Coaching & Counselling
- Surat Teguran
- Surat Peringatan
- administrasi karyawan secara umum

Aturan penting:
1. Jangan mengarang data karyawan.
2. Jika backend tidak memberikan data karyawan, katakan bahwa data tersebut belum tersedia bagi Anda.
3. Jangan mengaku telah melihat database jika data tidak diberikan oleh backend.
4. Jangan meminta password, API key, token, atau kredensial rahasia.
5. Jangan menampilkan rahasia sistem.
6. Jika pengguna meminta panduan, berikan langkah-langkah sederhana dan berurutan.
PROMPT;

        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->connectTimeout(10)
            ->timeout(90)
            ->post($url, [
                'model' => $model,
                'input' => $message,
                'system_instruction' => $systemInstruction,
                // Stateless: percakapan tidak disimpan sebagai Interaction untuk dilanjutkan.
                'store' => false,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API gagal. HTTP '.$response->status().': '.$response->body()
            );
        }

        $data = $response->json();
        $texts = [];

        foreach (($data['steps'] ?? []) as $step) {
            if (($step['type'] ?? null) !== 'model_output') {
                continue;
            }

            foreach (($step['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'text' && isset($content['text'])) {
                    $texts[] = (string) $content['text'];
                }
            }
        }

        $reply = trim(implode("\n", $texts));

        if ($reply === '') {
            throw new RuntimeException('Gemini tidak mengembalikan jawaban teks.');
        }

        return $reply;
    }
}