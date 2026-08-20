<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nomi Cose Citta') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                            <span class="text-xl font-bold text-gray-900">Nomi Cose Citta</span>
                        </a>
                    </div>

                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <a href="{{ route('login') }}" 
                           class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 {{ request()->routeIs('login') ? 'text-indigo-600' : '' }}">
                            Accedi
                        </a>
                        <a href="{{ route('register') }}" 
                           class="ml-4 px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 {{ request()->routeIs('register') ? 'text-indigo-600' : '' }}">
                            Registrati
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
