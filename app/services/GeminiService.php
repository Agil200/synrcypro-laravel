<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function ask(
        string $message,
        array $knowledge = []
    ): string {

        $apiKey = trim((string) config('services.gemini.key'));
        $model = trim((string) config('services.gemini.model', 'gemini-3.6-flash'));

        $url = rtrim(
            trim((string) config('services.gemini.url')),
            '/'
        );

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $knowledgeText = '';

        foreach ($knowledge as $item) {
            $knowledgeText .= "\nPertanyaan: ".$item['question'];
            $knowledgeText .= "\nJawaban resmi: ".$item['answer']."\n";
        }

        $systemInstruction = <<<PROMPT
Anda adalah MINA (Mining Intelligence Assistant), asisten AI internal SYNRGYPRO.

Gunakan Bahasa Indonesia yang profesional.

Gunakan Knowledge Base perusahaan berikut sebagai sumber utama:

$knowledgeText

Aturan:
1. Jika jawaban tersedia di Knowledge Base, gunakan jawaban tersebut.
2. Jangan mengarang aturan perusahaan.
3. Jangan membuat data karyawan palsu.
4. Jika informasi tidak tersedia, katakan dengan jelas.
PROMPT;

        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->timeout(90)
            ->post($url, [
                'model' => $model,
                'input' => $message,
                'system_instruction' => $systemInstruction,
                'store' => false,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API gagal: '.$response->body()
            );
        }

        $data = $response->json();
        $texts = [];

        foreach (($data['steps'] ?? []) as $step) {
            foreach (($step['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'text') {
                    $texts[] = $content['text'];
                }
            }
        }

        $reply = trim(implode("\n", $texts));

        if ($reply === '') {
            throw new RuntimeException('Gemini tidak mengembalikan jawaban.');
        }

        return $reply;
    }
}