<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatHistoryController extends Controller
{
    /**
     * Menampilkan halaman riwayat chat
     * Menampilkan daftar chat yang diurutkan dari yang terbaru
     * dengan pagination 10 item per halaman
     * 
     * @param Request $request Request yang berisi parameter filter dan sort
     */
    public function index(Request $request)
    {
        // Query untuk mengambil riwayat chat
        $query = ChatHistory::where('user_id', Auth::id());

        // Filter favorit
        if ($request->has('favorite')) {
            $query->where('is_favorite', true);
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_message', 'like', "%{$search}%")
                  ->orWhere('ai_response', 'like', "%{$search}%");
            });
        }

        // Pengurutan
        $sort = $request->get('sort', 'newest');
        $query->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc');

        // Pagination dengan mempertahankan query parameters
        $chatHistories = $query->paginate(20)->withQueryString();

        // Menampilkan view dengan data
        return view('chat-history.index', compact('chatHistories'));
    }

    /**
     * Mengubah status favorit dari sebuah chat
     * Toggle antara favorit dan tidak favorit
     * 
     * @param ChatHistory $chat Chat yang akan diubah status favoritnya
     */
    public function toggleFavorite(ChatHistory $chat)
    {
        // Pastikan user hanya bisa mengakses chat miliknya
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        // Mengubah status favorit
        $chat->update([
            'is_favorite' => !$chat->is_favorite
        ]);

        // Menampilkan pesan sukses
        return back()->with('success', 
            $chat->is_favorite ? 'Chat ditandai sebagai favorit!' : 'Chat dihapus dari favorit!'
        );
    }

    /**
     * Menghapus satu riwayat chat
     * 
     * @param ChatHistory $chat Chat yang akan dihapus
     */
    public function destroy(ChatHistory $chat)
    {
        // Pastikan user hanya bisa menghapus chat miliknya
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        // Menghapus chat
        $chat->delete();

        // Menampilkan pesan sukses
        return back()->with('success', 'Chat berhasil dihapus!');
    }

    /**
     * Menghapus beberapa riwayat chat sekaligus
     * 
     * @param Request $request Request yang berisi array ID chat yang akan dihapus
     */
    public function bulkDestroy(Request $request)
    {
        // Validasi input
        $request->validate([
            'chat_ids' => 'required|array',
            'chat_ids.*' => 'exists:chat_histories,id'
        ]);

        // Hapus chat yang dipilih (hanya milik user yang sedang login)
        ChatHistory::whereIn('id', $request->chat_ids)
                  ->where('user_id', Auth::id())
                  ->delete();

        return back()->with('success', 'Chat yang dipilih berhasil dihapus!');
    }
}
