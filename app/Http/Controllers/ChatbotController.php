<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function chat(
        Request $request,
        GeminiService $gemini,
        KnowledgeBaseService $knowledge
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $knowledgeData = $knowledge->search($validated['message']);

            // Gemini hanya menghasilkan teks jawaban.
            // Link resmi dikirim TERPISAH ke frontend agar tidak double/render HTML.
            $reply = $gemini->ask(
                $validated['message'],
                $knowledgeData
            );

            $links = collect($knowledgeData)
                ->map(function (array $item): ?array {
                    $url = trim((string) ($item['link'] ?? ''));

                    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                        return null;
                    }

                    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

                    if (!in_array($scheme, ['http', 'https'], true)) {
                        return null;
                    }

                    return [
                        'url' => $url,
                        'label' => $this->linkLabel($url),
                        'source' => trim((string) ($item['source'] ?? '')),
                    ];
                })
                ->filter()
                ->unique('url')
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'reply' => $reply,
                'links' => $links,
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'MINA sedang tidak dapat diakses. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function reset(): JsonResponse
    {
        // MINA saat ini stateless, jadi tidak ada history server yang perlu dihapus.
        return response()->json([
            'success' => true,
            'message' => 'Percakapan MINA direset.',
        ]);
    }

    private function linkLabel(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'whatsapp.com'),
            str_contains($host, 'wa.me') => 'Buka WhatsApp',

            str_contains($host, 'play.google.com') => 'Download Google Play',
            str_contains($host, 'apps.apple.com') => 'Buka App Store',
            str_contains($host, 'testflight.apple.com') => 'Buka TestFlight iOS',

            str_contains($host, 'docs.google.com') => 'Buka Form / Dokumen',
            str_contains($host, 'drive.google.com') => 'Buka Google Drive',

            str_contains($host, 'mediafire.com') => 'Download Aplikasi',
            str_contains($host, 'sites.google.com') => 'Buka Portal',
            str_contains($host, 'chatbase.co') => 'Buka Chatbot Comben',

            default => 'Buka Link Resmi',
        };
    }
}