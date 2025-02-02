<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-avatar" content="{{ Auth::user()->avatar ? asset('images/' . Auth::user()->avatar) : '' }}">
    @endauth
    <title>Ruang IT</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e293b;
            --secondary-color: #0f172a;
            --accent-color: #1e293b;
            --accent-hover: #334155;
            --text-light: #f8fafc;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            min-height: 56px;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.35rem;
            color: var(--text-light) !important;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0;
        }

        .navbar-brand .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            background-color: var(--accent-hover);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }

        .navbar-nav .nav-link {
            color: var(--text-light) !important;
            padding: 0.45rem 0.85rem;
            margin: 0 0.25rem;
            border-radius: 0.4rem;
            transition: all 0.3s ease;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
            opacity: 1;
        }
        
        .navbar-nav .nav-link.active {
            background-color: var(--accent-hover);
            font-weight: 500;
            opacity: 1;
        }
        
        .navbar-nav .nav-link i {
            margin-right: 0.4rem;
            font-size: 1rem;
        }

        .navbar-toggler {
            padding: 0.25rem 0.5rem;
            font-size: 0.95rem;
            border: none;
        }
        
        .dropdown-menu {
            background-color: var(--primary-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            border-radius: 0.4rem;
            margin-top: 0.35rem;
            padding: 0.35rem 0;
        }
        
        .dropdown-item {
            color: var(--text-light);
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
            color: var(--text-light);
        }
        
        .dropdown-item i {
            margin-right: 0.4rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .dropdown-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0.35rem 0;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            color: var(--text-light);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(30, 41, 59, 0.25);
        }
        
        .card {
            box-shadow: 0 0 15px rgba(0,0,0,.05);
            border: none;
            margin-top: 2rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f8f9fa;
            font-weight: bold;
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-md">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <div class="avatar">
                    <i class="bi bi-robot"></i>
                </div>
                Ruang IT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chatbot') ? 'active' : '' }}" href="{{ route('chatbot') }}">
                            <i class="bi bi-chat-dots"></i> Mr.Tecno
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chat-history.*') ? 'active' : '' }}" href="{{ route('chat-history.index') }}">
                            <i class="bi bi-clock-history"></i> Riwayat Chat
                        </a>
                    </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="bi bi-person"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>
