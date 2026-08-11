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
        $url = rtrim(trim((string) config('services.gemini.url')), '/');

        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        if ($url === '') {
            throw new RuntimeException('GEMINI_API_URL belum dikonfigurasi.');
        }

        $knowledgeText = $this->buildKnowledgeText($knowledge);

        $systemInstruction = <<<PROMPT
Anda adalah MINA (Mining Intelligence Assistant), asisten AI internal SYNRGYPRO PPA SITE BA.

Gunakan Bahasa Indonesia yang profesional, ringkas, jelas, dan mudah dipahami.

KNOWLEDGE BASE RESMI:
{$knowledgeText}

ATURAN WAJIB:
1. Jika informasi tersedia di Knowledge Base, gunakan informasi tersebut sebagai sumber utama.
2. Jangan mengarang aturan, prosedur, data, nomor kontak, atau kebijakan perusahaan.
3. Jangan membuat data karyawan palsu.
4. Jika informasi tidak tersedia, katakan bahwa informasi tersebut belum tersedia di Knowledge Base MINA.
5. Jawaban harus berupa TEKS BIASA.
6. JANGAN membuat HTML seperti <a>, <div>, <br>, style="", target="", atau tag HTML apa pun.
7. JANGAN menggunakan Markdown link seperti [Buka Link](https://...).
8. JANGAN menuliskan URL/link di dalam jawaban. Link resmi akan ditampilkan otomatis oleh aplikasi MINA.
9. Jangan menulis bagian "Link resmi:" karena aplikasi akan menampilkan tombol link secara terpisah.
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
            // Ambil hanya output model jika field type tersedia.
            if (isset($step['type']) && $step['type'] !== 'model_output') {
                continue;
            }

            foreach (($step['content'] ?? []) as $content) {
                if (
                    ($content['type'] ?? null) === 'text'
                    && isset($content['text'])
                ) {
                    $texts[] = (string) $content['text'];
                }
            }
        }

        $reply = trim(implode("\n", $texts));

        if ($reply === '') {
            throw new RuntimeException('Gemini tidak mengembalikan jawaban teks.');
        }

        // Guard terakhir: hilangkan HTML fence yang mungkin tetap dibuat model.
        $reply = strip_tags($reply);

        return trim($reply);
    }

    private function buildKnowledgeText(array $knowledge): string
    {
        if ($knowledge === []) {
            return 'Tidak ada data Knowledge Base yang cocok dengan pertanyaan pengguna.';
        }

        $blocks = [];

        foreach ($knowledge as $index => $item) {
            $number = $index + 1;

            $blocks[] = implode("\n", array_filter([
                "DATA {$number}",
                'Kategori: '.trim((string) ($item['category'] ?? '')),
                'Pertanyaan: '.trim((string) ($item['question'] ?? '')),
                'Jawaban resmi: '.trim((string) ($item['answer'] ?? '')),
                'Sumber: '.trim((string) ($item['source'] ?? '')),
            ], static fn (string $line): bool => !str_ends_with($line, ': ')));
        }

        return implode("\n\n", $blocks);
    }
}