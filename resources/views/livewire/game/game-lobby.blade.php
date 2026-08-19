<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $game->name }}</h1>
        <p class="text-gray-600 mt-1">Attendendo che tutti i giocatori si uniscano...</p>
    </div>

    <!-- Game Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold">{{ $players ? count($players) : 0 }}</span>
                </div>
                <p class="text-sm text-gray-600">Giocatori</p>
                <p class="text-xs text-gray-500">Max: {{ $game->max_players }}</p>
            </div>
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="text-2xl font-bold">{{ $game->settings['rounds'] ?? 5 }}</span>
                </div>
                <p class="text-sm text-gray-600">Round</p>
            </div>
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold">{{ $game->settings['round_duration'] ?? 60 }}s</span>
                </div>
                <p class="text-sm text-gray-600">Durata round</p>
            </div>
        </div>
    </div>

    <!-- Players List -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Giocatori</h2>
        
        <div class="space-y-4">
            @foreach($players as $player)
                <div wire:key="player-{{ $player['id'] }}" class="player-card flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($player['user']['name'], 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $player['user']['name'] }}</p>
                            <p class="text-sm text-gray-600">{{ $player['user']['email'] }}</p>
                        </div>
                    </div>
                    @if($player['user']['id'] === auth()->id())
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Tu</span>
                    @endif
                    @if($game->creator_id === $player['user']['id'])
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Creatore</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="flex flex-wrap gap-4">
                <button 
                    wire:click="copyInviteLink"
                    class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span>Copia link invito</span>
                </button>

                @if($game->isCreator(auth()->user()))
                    <button 
                        wire:click="leaveGame"
                        class="flex items-center space-x-2 bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Elimina partita</span>
                    </button>
                @else
                    <button 
                        wire:click="leaveGame"
                        class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Lascia partita</span>
                    </button>
                @endif
            </div>

            @if($canStart)
                <button 
                    wire:click="startGame"
                    class="btn-success text-white px-8 py-3 rounded-lg font-medium flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Avvia Partita</span>
                </button>
            @else
                <div class="text-gray-500 text-sm">
                    @if(!$isCreator)
                        Solo il creatore può avviare la partita
                    @else
                        Servono almeno 2 giocatori per avviare la partita
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
