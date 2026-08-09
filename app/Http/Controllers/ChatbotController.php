<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatbotController extends Controller
{
    public function chat(Request $request, GeminiService $gemini): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $reply = $gemini->ask($validated['message']);

            return response()->json([
                'success' => true,
                'reply' => $reply,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'SYNRGY Assistant sedang tidak dapat diakses. Silakan coba lagi.',
            ], 500);
        }
    }

    public function reset(): JsonResponse
    {
        // Versi ini stateless, jadi tidak ada riwayat server yang perlu dihapus.
        return response()->json([
            'success' => true,
            'message' => 'Percakapan direset.',
        ]);
    }
}