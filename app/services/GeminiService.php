<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function ask(string $message, array $knowledge = []): string
    {
        $apiKey = trim((string) config('services.gemini.key'));
        $model = trim((string) config('services.gemini.model', 'gemini-3.6-flash'));
        $url = rtrim((string) config('services.gemini.url'), '/');

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $knowledgeText = '';

        foreach ($knowledge as $item) {
            $knowledgeText .= "\nPertanyaan: " . ($item['question'] ?? '');
            $knowledgeText .= "\nJawaban resmi: " . ($item['answer'] ?? '');

            if (!empty($item['link'])) {
                $knowledgeText .= "\nLink resmi: " . $item['link'];
            }

            if (!empty($item['source'])) {
                $knowledgeText .= "\nSumber: " . $item['source'];
            }

            $knowledgeText .= "\n";
        }

        $systemInstruction = <<<PROMPT
Anda adalah MINA (Mining Intelligence Assistant), asisten AI internal SYNRGYPRO.

Gunakan Knowledge Base perusahaan sebagai sumber utama.

Knowledge Base:
$knowledgeText

Aturan:
1. Gunakan informasi dari Knowledge Base.
2. Jangan mengarang informasi.
3. Jika tersedia link resmi, WAJIB tampilkan.
4. Jangan menghapus URL.
5. Tampilkan link aplikasi/form/website/WhatsApp pada baris terpisah.
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
            throw new RuntimeException('Gemini API gagal: '.$response->body());
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
