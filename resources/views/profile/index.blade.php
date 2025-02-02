{{-- 
    View untuk halaman profil user dengan fitur:
    1. Tampilan informasi profil (nama, email)
    2. Upload dan preview avatar
    3. Tampilan riwayat chat terakhir
    4. Pagination untuk riwayat chat
    
    Library yang digunakan:
    - Bootstrap 5 untuk layout dan komponen UI
    - Bootstrap Icons untuk icon-icon
    - asset() helper untuk URL gambar
--}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Flash message untuk notifikasi sukses/error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Card untuk profil --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profil Saya</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            {{-- Preview avatar --}}
                            @if($user->avatar)
                                <img src="{{ asset('images/' . $user->avatar) }}" alt="Avatar" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                    <span class="text-white h1">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            {{-- Informasi profil --}}
                            <h4>{{ $user->name }}</h4>
                            <p class="text-muted">{{ $user->email }}</p>
                            @if($user->bio)
                                <p>{{ $user->bio }}</p>
                            @endif
                            @if($user->phone)
                                <p><i class="bi bi-telephone"></i> {{ $user->phone }}</p>
                            @endif
                            <div class="mt-3">
                                {{-- Link edit profil --}}
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profil</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card untuk riwayat chat --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Chat</h5>
                    <a href="{{ route('chat-history.index') }}" class="btn btn-outline-primary btn-sm">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    {{-- Daftar chat terakhir --}}
                    @forelse($chatHistories as $chat)
                        <div class="chat-item mb-4 p-3 border rounded {{ $chat->is_favorite ? 'border-warning' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <small class="text-muted">{{ $chat->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <div>
                                    {{-- Tombol favorit --}}
                                    <form action="{{ route('chat-history.toggle-favorite', $chat) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-link p-0">
                                            <i class="bi {{ $chat->is_favorite ? 'bi-star-fill text-warning' : 'bi-star' }}"></i>
                                        </button>
                                    </form>
                                    {{-- Tombol hapus --}}
                                    <form action="{{ route('chat-history.destroy', $chat) }}" method="POST" class="d-inline ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 text-danger" onclick="return confirm('Yakin ingin menghapus chat ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="user-message mb-2">
                                <strong>Anda:</strong>
                                <p class="mb-0">{{ $chat->user_message }}</p>
                            </div>
                            <div class="ai-message">
                                <strong>Mr.Tecno:</strong>
                                <div class="mb-0">{!! Str::markdown($chat->ai_response) !!}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted">Belum ada riwayat chat.</p>
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
@endsection
