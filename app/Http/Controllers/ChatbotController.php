<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatHistory;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $apiKey;
    protected $endpoint;

    public function __construct()
    {
        $this->apiKey = 'AIzaSyB8e4RxCTRs6BOZFrKk6C9aNkGBqOxPPr4';
        $this->endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->apiKey;
    }

    /**
     * Menampilkan halaman chatbot
     */
    public function index()
    {
        $user = Auth::user();
        return view('chatbot', compact('user'));
    }

    /**
     * Memproses pesan dari user dan mendapatkan respons dari AI
     */
    public function chat(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            // Membuat atau mendapatkan percakapan saat ini
            $conversation = Conversation::create([
                'user_id' => Auth::id(),
                'title' => 'Chat with Mr.Tecno ' . now()->format('Y-m-d H:i:s')
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Kamu adalah Mr.Tecno, asisten AI yang ramah dan membantu. Selalu gunakan Bahasa Indonesia yang sopan dan informal dalam merespons. Format jawabanmu menggunakan markdown. Gunakan # untuk judul, * untuk list, ``` untuk kode, dan * atau ** untuk penekanan. Berikut adalah pesan dari user: " . $request->input('message')]
                        ]
                    ]
                ]
            ]);

            if (!$response->successful()) {
                \Log::error('Gemini API Error: ' . $response->body());
                throw new \Exception('Gagal mendapatkan respons dari AI: ' . $response->status());
            }

            $result = $response->json();
            
            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                \Log::error('Unexpected Gemini API Response: ' . json_encode($result));
                throw new \Exception('Format respons AI tidak sesuai');
            }

            $message = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean up the response
            $message = preg_replace('/^Kamu adalah.+?pesan dari user:\s*/s', '', $message);
            
            // Menyimpan riwayat chat
            ChatHistory::create([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'user_message' => $request->input('message'),
                'ai_response' => $message,
                'sequence' => ChatHistory::where('conversation_id', $conversation->id)->count() + 1
            ]);

            return response()->json([
                'response' => $message
            ]);

        } catch (\Exception $e) {
            \Log::error('Chat Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus riwayat chat
     */
    public function destroy(ChatHistory $chat)
    {
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        $chat->delete();
        return back()->with('success', 'Chat berhasil dihapus');
    }

    /**
     * Menampilkan halaman riwayat chat
     */
    public function history()
    {
        $chats = Auth::user()
            ->chatHistories()
            ->with('conversation')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($chat) {
                return $chat->created_at->format('d M Y');
            });

        return view('chatbot.history', compact('chats'));
    }
}