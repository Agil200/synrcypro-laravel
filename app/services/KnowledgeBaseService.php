<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class KnowledgeBaseService
{
    public function search(string $message): array
    {
        $spreadsheetId = config('services.knowledge_base.spreadsheet_id');
        $range = config('services.knowledge_base.range');
        $apiKey = config('services.knowledge_base.api_key');

        if (!$spreadsheetId) {
            throw new RuntimeException('GOOGLE_KNOWLEDGE_SHEET_ID belum dikonfigurasi.');
        }

        if (!$apiKey) {
            throw new RuntimeException('GOOGLE_API_KEY belum dikonfigurasi.');
        }

        $response = Http::get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}",
            [
                'key' => $apiKey
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Google Sheet gagal dibaca: '.$response->body()
            );
        }

        $rows = $response->json('values', []);

        if (count($rows) <= 1) {
            return [];
        }

        $headers = array_map(
            fn($value) => strtolower(trim($value)),
            $rows[0]
        );

        $keywordIndex = array_search('keywords', $headers);
        $questionIndex = array_search('pertanyaan', $headers);
        $answerIndex = array_search('jawaban', $headers);

        if ($keywordIndex === false || $answerIndex === false) {
            throw new RuntimeException('Kolom Knowledge Base tidak sesuai.');
        }

        $messageLower = strtolower($message);
        $results = [];

        foreach (array_slice($rows, 1) as $row) {

            $keywords = strtolower($row[$keywordIndex] ?? '');
            $answer = $row[$answerIndex] ?? '';
            $question = $row[$questionIndex] ?? '';

            foreach (explode(',', $keywords) as $keyword) {

                $keyword = trim($keyword);

                if ($keyword !== '' && str_contains($messageLower, $keyword)) {

                    $results[] = [
                        'question' => $question,
                        'answer' => $answer,
                    ];

                    break;
                }
            }
        }

        return $results;
    }
}