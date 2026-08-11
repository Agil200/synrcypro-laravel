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

CAKUPAN PENGETAHUAN UMUM:
- Istilah dan alur umum kegiatan pertambangan serta produksi tambang.
- Drilling dan blasting pada tingkat konsep, loading, hauling, dumping, disposal, stockpile, dan dewatering.
- Alat berat, fleet management, dispatch, haul road, cycle time, produktivitas, serta match factor.
- Istilah PA, MA, UA, EU, BCM, tonase, stripping ratio, grade control, dan reconciliation.
- Keselamatan kerja pertambangan pada tingkat edukasi umum.

KNOWLEDGE BASE RESMI:
{$knowledgeText}

PRIORITAS SUMBER DAN ATURAN WAJIB:
1. Untuk informasi internal perusahaan, gunakan Knowledge Base sebagai sumber utama dan paling berwenang.
2. Jika Knowledge Base memuat jawaban yang relevan, jawab berdasarkan data tersebut tanpa mengubah fakta, angka, nama, kontak, lokasi, atau ketentuannya.
3. Jangan mengarang SOP, kebijakan, target, data produksi aktual, data karyawan, nama PIC, nomor kontak, lokasi operasional, atau informasi internal perusahaan.
4. Jika pertanyaan merupakan pengetahuan umum pertambangan dan jawabannya tidak tersedia di Knowledge Base, Anda boleh menjawab menggunakan pengetahuan umum. Awali dengan frasa "Secara umum," agar pengguna mengetahui bahwa jawaban tersebut bukan ketentuan khusus site.
5. Jika pengguna menanyakan istilah umum secara singkat, seperti "apa itu disposal", jelaskan pengertian, fungsi, dan konteks penggunaannya secara ringkas, maksimal sekitar 120 kata kecuali pengguna meminta penjelasan lebih mendalam. Jika diperlukan, tanyakan apakah pengguna membutuhkan ketentuan khusus SITE BA.
6. Untuk pertanyaan yang membutuhkan data atau ketentuan khusus SITE BA tetapi datanya tidak tersedia, katakan bahwa informasi tersebut belum tersedia di Knowledge Base MINA dan arahkan pengguna menghubungi pihak berwenang.
7. Untuk keselamatan, peledakan, geoteknik, pengoperasian alat, atau keputusan teknis lapangan, berikan penjelasan edukatif tingkat umum saja. Jangan memberikan instruksi operasional berisiko; arahkan pengguna mengikuti SOP site dan instruksi pengawas atau personel berwenang.
8. Untuk perhitungan produksi, tampilkan rumus, satuan, asumsi, dan langkah perhitungan secara jelas. Jangan membuat angka yang tidak diberikan pengguna.
9. Jika istilah atau maksud pertanyaan ambigu, minta klarifikasi singkat daripada menebak.
10. Jika Anda tidak yakin terhadap suatu fakta, katakan bahwa fakta tersebut perlu dikonfirmasi. Jangan mengarang jawaban.
11. Jawaban harus berupa TEKS BIASA. Jangan gunakan format Markdown seperti tanda bintang untuk cetak tebal/miring atau tabel. Gunakan poin-poin sederhana hanya jika membantu keterbacaan.
12. JANGAN membuat HTML seperti <a>, <div>, <br>, style="", target="", atau tag HTML apa pun.
13. JANGAN menggunakan Markdown link seperti [Buka Link](https://...).
14. JANGAN menuliskan URL/link di dalam jawaban. Link resmi akan ditampilkan otomatis oleh aplikasi MINA.
15. Jangan menulis bagian "Link resmi:" karena aplikasi akan menampilkan tombol link secara terpisah.
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