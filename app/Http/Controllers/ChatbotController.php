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

            $knowledgeData = $knowledge->search(
                $validated['message']
            );

            $reply = $gemini->ask(
                $validated['message'],
                $knowledgeData
            );

            return response()->json([
                'success' => true,
                'reply' => $reply,
            ]);

        } catch (Throwable $e) {

            report($e);


            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function reset(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Percakapan MINA direset.',
        ]);
    }
}