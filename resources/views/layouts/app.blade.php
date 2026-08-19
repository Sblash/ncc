<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Nomi Cose Citta') }}</title>
    <meta name="description" content="Gioca a Nomi Cose Citta online con i tuoi amici">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
        
        .letter-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
        }
        
        .timer-bar {
            height: 8px;
            background: linear-gradient(90deg, #ef4444, #f97316, #eab308, #22c55e);
            border-radius: 4px;
        }
        
        .word-card {
            transition: all 0.3s ease;
        }
        
        .word-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .player-card {
            transition: all 0.2s ease;
        }
        
        .player-card:hover {
            transform: scale(1.02);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .game-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .game-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .score-badge {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .round-badge {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2 text-white">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-lg">NCC</span>
                        </div>
                        <span class="text-xl font-bold">Nomi Cose Città</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="/games" class="text-white hover:text-blue-200 transition-colors">Partite</a>
                        <a href="/games/create" class="text-white hover:text-blue-200 transition-colors">Crea Partita</a>
                        <a href="/stats" class="text-white hover:text-blue-200 transition-colors">Statistiche</a>
                        <div class="relative">
                            <button 
                                x-data="{ open: false }" 
                                @click="open = !open"
                                class="text-white hover:text-blue-200 transition-colors flex items-center space-x-1"
                            >
                                <span>{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                x-cloak
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                            >
                                <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profilo</a>
                                <a href="/settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Impostazioni</a>
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Esci</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="/login" class="text-white hover:text-blue-200 transition-colors">Accedi</a>
                        <a href="/register" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors">Registrati</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400">© {{ date('Y') }} Nomi Cose Città. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    @stack('scripts')
</body>
</html>
