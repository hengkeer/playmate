<?php

namespace App\Http\Controllers;

use App\Exceptions\GeminiException;
use App\Exceptions\RagIndexMissingException;
use App\Models\ChatSession;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatAssistantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sessions = ChatSession::where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->get(['id', 'title', 'updated_at']);

        return response()->json($sessions);
    }

    public function store(Request $request): JsonResponse
    {
        $session = ChatSession::create([
            'user_id' => $request->user()->id,
            'title'   => null,
        ]);

        return response()->json($session, 201);
    }

    public function messages(Request $request, ChatSession $session): JsonResponse
    {
        $this->authorizeOwner($request, $session);

        return response()->json($session->messages()->get());
    }

    public function send(Request $request, ChatSession $session, ChatbotService $bot): JsonResponse
    {
        $this->authorizeOwner($request, $session);

        $data = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $userMessage = $session->messages()->create([
            'role'    => 'user',
            'content' => $data['content'],
        ]);

        try {
            $assistant = $bot->ask($session, $data['content']);
            return response()->json([
                'user'      => $userMessage,
                'assistant' => $assistant,
                'session'   => $session->fresh(),
            ]);
        } catch (GeminiException $e) {
            Log::warning('Chatbot Gemini error', ['msg' => $e->getMessage()]);
            return response()->json([
                'error'   => 'AI service unavailable. Please try again.',
                'user_id' => $userMessage->id,
            ], 502);
        }
    }

    public function destroy(Request $request, ChatSession $session): JsonResponse
    {
        $this->authorizeOwner($request, $session);
        $session->delete();
        return response()->json(['ok' => true]);
    }

    protected function authorizeOwner(Request $request, ChatSession $session): void
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
