<div class="py-12" x-data="{ poll: null }" x-init="
    poll = setInterval(() => {
        @this.call('loadGame');
    }, 5000);
" x-on:game-started.window="clearInterval(poll);">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Lobby: {{ $game['name'] ?? 'N/A' }}</h1>
                <a href="{{ route('games.index') }}" 
                   class="text-indigo-600 hover:text-indigo-900">
                    Torna alle partite
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Informazioni Partita</h2>
                    <div class="space-y-2">
                        <p><strong>Creatore:</strong> {{ $game['creator']['name'] ?? 'N/A' }}</p>
                        <p><strong>Max Giocatori:</strong> {{ $game['max_players'] ?? 'N/A' }}</p>
                        <p><strong>Stato:</strong> 
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                {{ ucfirst($game['status'] ?? 'unknown') }}
                            </span>
                        </p>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Giocatori</h2>
                    <ul class="space-y-2">
                        @foreach($players as $player)
                            <li class="flex items-center space-x-3">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                <span>{{ $player['user']['name'] ?? 'N/A' }}</span>
                                @if(($player['user']['id'] ?? null) === (session('auth_user.id') ?? null))
                                    <span class="text-xs text-gray-500">(Tu)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ count($players) }} / {{ $game['max_players'] ?? 'N/A' }} giocatori
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-between items-center">
                @if($isCreator)
                    <button wire:click="startGame"
                            wire:loading.attr="disabled"
                            :disabled="!$canStart"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        @if($canStart)
                            Avvia Partita
                        @else
                            Aspetta altri giocatori...
                        @endif
                    </button>
                @else
                    <p class="text-gray-600">In attesa che il creatore avvii la partita...</p>
                @endif

                <button wire:click="leaveGame"
                        class="text-gray-600 hover:text-gray-900">
                    Abbandona Partita
                </button>
            </div>
        </div>
    </div>
</div>
