<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class KnowledgeBaseService
{
    public function search(string $message): array
    {
        $spreadsheetId = trim((string) config('services.knowledge_base.spreadsheet_id'));
        $range = trim((string) config(
            'services.knowledge_base.range',
            'SYNRGY_AI_KNOWLEDGE!A:H'
        ));
        $apiKey = trim((string) config('services.knowledge_base.api_key'));

        if ($spreadsheetId === '') {
            throw new RuntimeException('GOOGLE_KNOWLEDGE_SHEET_ID belum dikonfigurasi.');
        }

        if ($range === '') {
            throw new RuntimeException('GOOGLE_KNOWLEDGE_RANGE belum dikonfigurasi.');
        }

        if ($apiKey === '') {
            throw new RuntimeException('GOOGLE_API_KEY belum dikonfigurasi.');
        }

        $encodedRange = rawurlencode($range);

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$encodedRange}",
                ['key' => $apiKey]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Google Sheet gagal dibaca. HTTP '
                .$response->status()
                .': '
                .$response->body()
            );
        }

        $rows = $response->json('values', []);

        if (!is_array($rows) || count($rows) <= 1) {
            return [];
        }

        $headers = array_map(
            static fn ($value): string => mb_strtolower(trim((string) $value)),
            $rows[0]
        );

        $idIndex = array_search('id', $headers, true);
        $categoryIndex = array_search('kategori', $headers, true);
        $keywordIndex = array_search('keywords', $headers, true);
        $questionIndex = array_search('pertanyaan', $headers, true);
        $answerIndex = array_search('jawaban', $headers, true);
        $linkIndex = array_search('link', $headers, true);
        $sourceIndex = array_search('sumber', $headers, true);
        $statusIndex = array_search('status', $headers, true);

        if (
            $keywordIndex === false
            || $questionIndex === false
            || $answerIndex === false
        ) {
            throw new RuntimeException(
                'Header Knowledge Base wajib memiliki KEYWORDS, PERTANYAAN, dan JAWABAN.'
            );
        }

        $messageNormalized = $this->normalize($message);
        $messageTokens = $this->tokens($messageNormalized);

        $results = [];

        foreach (array_slice($rows, 1) as $row) {
            $status = $this->cell($row, $statusIndex);

            if (!$this->isActive($status)) {
                continue;
            }

            $id = $this->cell($row, $idIndex);
            $category = $this->cell($row, $categoryIndex);
            $keywords = $this->cell($row, $keywordIndex);
            $question = $this->cell($row, $questionIndex);
            $answer = $this->cell($row, $answerIndex);
            $link = $this->cell($row, $linkIndex);
            $source = $this->cell($row, $sourceIndex);

            if ($answer === '') {
                continue;
            }

            $score = $this->scoreRow(
                $messageNormalized,
                $messageTokens,
                $keywords,
                $question
            );

            if ($score <= 0) {
                continue;
            }

            $results[] = [
                'id' => $id,
                'category' => $category,
                'question' => $question,
                'answer' => $answer,
                'link' => $link,
                'source' => $source,
                'status' => $status,
                '_score' => $score,
            ];
        }

        usort(
            $results,
            static fn (array $a, array $b): int => $b['_score'] <=> $a['_score']
        );

        $unique = [];
        $seen = [];

        foreach ($results as $item) {
            $key = $item['id'] !== ''
                ? 'id:'.$item['id']
                : 'qa:'.md5($item['question'].'|'.$item['answer']);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            unset($item['_score']);
            $unique[] = $item;

            // Batasi context agar tidak mengirim terlalu banyak data ke Gemini.
            if (count($unique) >= 3) {
                break;
            }
        }

        return $unique;
    }

    private function cell(array $row, int|false $index): string
    {
        if ($index === false) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    private function isActive(string $status): bool
    {
        if ($status === '') {
            return true;
        }

        return in_array(
            mb_strtolower(trim($status)),
            ['aktif', 'active', '1', 'yes', 'ya'],
            true
        );
    }

    private function scoreRow(
        string $message,
        array $messageTokens,
        string $keywords,
        string $question
    ): int {
        $score = 0;
        $questionNormalized = $this->normalize($question);

        if ($questionNormalized !== '' && $questionNormalized === $message) {
            $score += 100;
        }

        foreach (explode(',', $keywords) as $keyword) {
            $keyword = $this->normalize($keyword);

            if ($keyword === '') {
                continue;
            }

            if (str_contains($message, $keyword)) {
                // Phrase yang lebih panjang dianggap lebih spesifik.
                $score += 10 + min(10, count($this->tokens($keyword)));
            }
        }

        if ($questionNormalized !== '') {
            foreach ($messageTokens as $token) {
                if (mb_strlen($token) >= 3 && str_contains($questionNormalized, $token)) {
                    $score += 2;
                }
            }
        }

        return $score;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\/+\-]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function tokens(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(
            preg_split('/\s+/u', $value) ?: [],
            static fn (string $token): bool => $token !== ''
        ));
    }
}