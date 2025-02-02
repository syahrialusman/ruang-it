@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages">
                    <div class="message bot">
                        <div class="avatar">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="message-content">
                            <p class="mb-0">Halo! Saya Mr.Tecno, asisten AI yang siap membantu. Ada yang bisa saya bantu?</p>
                        </div>
                    </div>
                </div>

                <div class="chat-input">
                    <form id="chat-form" class="d-flex gap-2">
                        @csrf
                        <input type="text" id="user-input" class="form-control" placeholder="Tanyakan sesuatu ke Mr.Tecno..." required>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #1e293b;
        --secondary-color: #0f172a;
        --accent-color: #1e293b;
        --accent-hover: #334155;
        --text-light: #f8fafc;
        --bg-light: #ffffff;
        --bg-secondary: #f1f5f9;
        --border-color: #e2e8f0;
    }

    .chat-container {
        height: calc(100vh - 200px);
        background-color: var(--bg-light);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .chat-messages {
        height: calc(100% - 70px);
        overflow-y: auto;
        padding: 20px;
        background-color: var(--bg-light);
    }
    .chat-input {
        padding: 15px 20px;
        background-color: var(--bg-secondary);
        border-top: 1px solid var(--border-color);
    }
    .message {
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .message.user {
        flex-direction: row;
        justify-content: flex-end;
    }
    .message-content {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 15px;
        background-color: var(--bg-secondary);
        color: var(--primary-color);
    }
    .user .message-content {
        background-color: var(--primary-color);
        color: var(--text-light);
        border-top-right-radius: 4px;
    }
    .bot .message-content {
        border-top-left-radius: 4px;
    }
    .avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background-color: var(--bg-secondary);
        color: var(--primary-color);
    }
    .user .avatar {
        background-color: var(--primary-color);
        color: var(--text-light);
    }
    .bot .avatar {
        background-color: var(--accent-hover);
        color: var(--text-light);
    }
    .form-control {
        background-color: var(--bg-light);
        border: 1px solid var(--border-color);
        color: var(--primary-color);
    }
    .form-control:focus {
        background-color: var(--bg-light);
        border-color: var(--accent-hover);
        color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(51, 65, 85, 0.25);
    }
    .btn-primary {
        background-color: var(--accent-color);
        border: none;
    }
    .btn-primary:hover {
        background-color: var(--accent-hover);
    }
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: var(--bg-secondary);
    }
    ::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
    .message-content pre {
        background-color: var(--bg-secondary);
        padding: 10px;
        border-radius: 5px;
        margin-top: 5px;
        color: var(--primary-color);
        overflow-x: auto;
        border: 1px solid var(--border-color);
    }
    .message-content code {
        color: var(--primary-color);
        background-color: var(--bg-secondary);
        padding: 2px 5px;
        border-radius: 3px;
        border: 1px solid var(--border-color);
    }
    .loading-container {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        margin: 1rem 0;
    }
    .typing-indicator {
        display: flex;
        gap: 0.5rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 1rem;
        border-bottom-left-radius: 0.25rem;
        border: 1px solid var(--border-color);
    }
    .dot {
        width: 8px;
        height: 8px;
        background: var(--primary-color);
        border-radius: 50%;
        animation: bounce 1.3s linear infinite;
        opacity: 0.7;
    }
    @keyframes bounce {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-4px); }
    }
</style>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    document.getElementById('chat-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const userInput = document.getElementById('user-input');
        const message = userInput.value;
        userInput.value = '';
        
        // Add user message
        addMessage(message, 'user');

        // Add loading animation after user message
        const loadingContainer = document.createElement('div');
        loadingContainer.className = 'loading-container';
        loadingContainer.innerHTML = `
            <div class="typing-indicator">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <small class="text-muted">Mr.Tecno sedang mengetik...</small>
        `;
        document.getElementById('chat-messages').appendChild(loadingContainer);
        document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
        
        try {
            const response = await fetch('/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message })
            });
            
            const data = await response.json();
            
            // Remove loading animation
            loadingContainer.remove();
            
            // Add bot response
            if (data.error) {
                addMessage(data.error, 'bot', true);
            } else {
                addMessage(data.response, 'bot');
            }
            
        } catch (error) {
            console.error('Error:', error);
            // Remove loading animation
            loadingContainer.remove();
            addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', 'bot', true);
        }
    });

    function addMessage(message, type, isError = false) {
        const chatMessages = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        
        const avatar = document.createElement('div');
        avatar.className = 'avatar';

        // Get user avatar URL from meta tag
        const userAvatar = document.querySelector('meta[name="user-avatar"]')?.content;
        
        if (type === 'user') {
            if (userAvatar) {
                avatar.innerHTML = `<img src="${userAvatar}" alt="User Avatar" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                avatar.innerHTML = '<i class="bi bi-person"></i>';
            }
        } else {
            avatar.innerHTML = '<i class="bi bi-robot"></i>';
        }
        
        const content = document.createElement('div');
        content.className = 'message-content';
        
        if (type === 'bot' && !isError) {
            // Remove any potential header formatting from the message
            let cleanMessage = message.replace(/^#\s.*?\n/, '');
            content.innerHTML = marked.parse(cleanMessage);
        } else {
            const p = document.createElement('p');
            p.className = 'mb-0';
            p.textContent = message;
            content.appendChild(p);
        }
        
        if (type === 'user') {
            messageDiv.appendChild(content);
            messageDiv.appendChild(avatar);
        } else {
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
        }
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
</script>
@endpush
@endsection
