{{-- 
    View untuk menampilkan riwayat chat dengan fitur:
    1. Pencarian chat berdasarkan isi pesan
    2. Filter chat favorit
    3. Pengurutan berdasarkan waktu (terbaru/terlama)
    4. Bulk delete untuk menghapus multiple chat
    5. Toggle favorite untuk menandai chat penting
    
    Library yang digunakan:
    - Bootstrap 5 untuk layout dan komponen UI
    - Bootstrap Icons untuk icon-icon
    - Str::markdown() untuk render markdown di respons AI
--}}

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            {{-- Flash message untuk notifikasi sukses --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                {{-- Header dengan filter Semua/Favorit --}}
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Riwayat Chat</h5>
                        <div class="btn-group">
                            <a href="{{ route('chat-history.index') }}" class="btn btn-outline-primary {{ !request('favorite') ? 'active' : '' }}">
                                Semua
                            </a>
                            <a href="{{ route('chat-history.index', ['favorite' => true]) }}" class="btn btn-outline-primary {{ request('favorite') ? 'active' : '' }}">
                                Favorit
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Search dan Sort Controls --}}
                    <div class="row mb-4">
                        {{-- Form pencarian --}}
                        <div class="col-md-6">
                            <form action="{{ route('chat-history.index') }}" method="GET" class="d-flex">
                                @if(request('favorite'))
                                    <input type="hidden" name="favorite" value="true">
                                @endif
                                <input type="text" name="search" class="form-control me-2" placeholder="Cari dalam chat..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </form>
                        </div>
                        {{-- Bulk delete dan pengurutan --}}
                        <div class="col-md-6 text-end">
                            <form id="bulk-delete-form" action="{{ route('chat-history.bulk-destroy') }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <div id="selected-chats-container"></div>
                                <button type="submit" class="btn btn-danger" id="bulk-delete-btn" disabled onclick="return confirm('Yakin ingin menghapus chat yang dipilih?')">
                                    Hapus yang Dipilih
                                </button>
                            </form>
                            <div class="btn-group ms-2">
                                <a href="{{ route('chat-history.index', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" 
                                   class="btn btn-outline-secondary {{ request('sort', 'newest') === 'newest' ? 'active' : '' }}">
                                    Terbaru
                                </a>
                                <a href="{{ route('chat-history.index', array_merge(request()->except('sort'), ['sort' => 'oldest'])) }}" 
                                   class="btn btn-outline-secondary {{ request('sort') === 'oldest' ? 'active' : '' }}">
                                    Terlama
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Chat --}}
                    @forelse($chatHistories as $chat)
                        <div class="chat-item mb-4 p-3 border rounded {{ $chat->is_favorite ? 'border-warning' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input chat-checkbox" data-id="{{ $chat->id }}">
                                    </div>
                                    <small class="text-muted ms-2">{{ $chat->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <div>
                                    <form action="{{ route('chat-history.destroy', $chat) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 text-danger" onclick="return confirm('Yakin ingin menghapus chat ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    {{-- Toggle Favorite Button --}}
                                    <form action="{{ route('chat-history.toggle-favorite', $chat) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-link p-0">
                                            <i class="bi {{ $chat->is_favorite ? 'bi-star-fill text-warning' : 'bi-star' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            {{-- Isi Chat --}}
                            <div class="chat-content">
                                <div class="user-message mb-2">
                                    <strong>Anda:</strong>
                                    <p class="mb-0">{{ $chat->user_message }}</p>
                                </div>
                                <div class="ai-message">
                                    <strong>Mr.Tecno:</strong>
                                    <div class="mb-0">{!! Str::markdown($chat->ai_response) !!}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <p>Belum ada riwayat chat.</p>
                        </div>
                    @endforelse

                    {{-- Pagination dengan informasi halaman --}}
                    @if($chatHistories->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                Menampilkan {{ $chatHistories->firstItem() ?? 0 }} sampai {{ $chatHistories->lastItem() ?? 0 }}
                                dari {{ $chatHistories->total() }} data
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($chatHistories->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">Previous</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $chatHistories->previousPageUrl() }}" rel="prev">Previous</a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($chatHistories->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $chatHistories->nextPageUrl() }}" rel="next">Next</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">Next</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styling khusus untuk tampilan chat history --}}
<style>
    /* Styling untuk item chat */
    .chat-item {
        background-color: #fff;
        transition: all 0.3s ease;
    }
    .chat-item:hover {
        background-color: #f8f9fa;
    }
    .chat-item.border-warning {
        background-color: #fff9e6;
    }
    
    /* Styling untuk bubble chat */
    .user-message, .ai-message {
        padding: 10px 15px;
        border-radius: 8px;
        margin: 5px 0;
    }
    .user-message {
        background-color: #e9ecef;
    }
    .ai-message {
        background-color: #f8f9fa;
    }
    .ai-message p {
        white-space: pre-line;
        margin-bottom: 0;
    }
    
    /* Styling untuk pagination */
    .pagination {
        margin-bottom: 0;
    }
    .page-link {
        color: #1e293b;
        border-color: #e2e8f0;
        padding: 0.5rem 1rem;
        background-color: #fff;
    }
    .page-link:hover {
        color: #0f172a;
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }
    .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    .page-item:first-child .page-link {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    .page-item:last-child .page-link {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
</style>

@push('scripts')
<script>
    // Handling checkbox untuk bulk delete
    const checkboxes = document.querySelectorAll('.chat-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectedChatsContainer = document.getElementById('selected-chats-container');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');

    // Event listener untuk setiap checkbox
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteButton);
    });

    // Update status tombol bulk delete dan hidden inputs
    function updateBulkDeleteButton() {
        const selectedCheckboxes = document.querySelectorAll('.chat-checkbox:checked');
        bulkDeleteBtn.disabled = selectedCheckboxes.length === 0;

        // Update hidden inputs untuk form bulk delete
        selectedChatsContainer.innerHTML = '';
        selectedCheckboxes.forEach(checkbox => {
            const chatId = checkbox.getAttribute('data-id');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'chat_ids[]';
            input.value = chatId;
            selectedChatsContainer.appendChild(input);
        });
    }

    // Event listener untuk form submit
    bulkDeleteForm.addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.chat-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu chat untuk dihapus');
            return false;
        }
        return confirm('Yakin ingin menghapus chat yang dipilih?');
    });
</script>
@endpush
@endsection
