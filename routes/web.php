<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatHistoryController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Redirect root to chatbot
Route::get('/', function () {
    return redirect()->route('chatbot');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Chatbot routes
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
    Route::post('/chat', [ChatbotController::class, 'chat'])->name('chat');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    
    // Chat history routes
    Route::get('/chat-history', [ChatHistoryController::class, 'index'])->name('chat-history.index');
    Route::put('/chat-history/{chat}/favorite', [ChatHistoryController::class, 'toggleFavorite'])->name('chat-history.toggle-favorite');
    Route::delete('/chat-history/{chat}', [ChatHistoryController::class, 'destroy'])->name('chat-history.destroy');
    Route::delete('/chat-history', [ChatHistoryController::class, 'bulkDestroy'])->name('chat-history.bulk-destroy');
    
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
