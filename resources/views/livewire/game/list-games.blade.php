<div class="space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Partite Disponibili</h1>
            <p class="text-gray-600 mt-1">Trova una partita a cui unirti o crea la tua</p>
        </div>
        <div class="flex space-x-4">
            <a href="/games/create" class="btn-primary text-white px-6 py-2 rounded-lg font-medium">
                Crea Partita
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-48">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
                <input 
                    wire:model.lazy="search" 
                    type="text" 
                    id="search" 
                    placeholder="Cerca partite..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div>
                <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filtra</label>
                <select 
                    wire:model.lazy="filter" 
                    id="filter" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="all">Tutte le partite</option>
                    <option value="waiting">In attesa</option>
                    <option value="started">In corso</option>
                    <option value="finished">Terminate</option>
                    <option value="my_games">Le mie partite</option>
                </select>
            </div>
            <div>
                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">Per pagina</label>
                <select 
                    wire:model.lazy="perPage" 
                    id="perPage" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Games List -->
    <div class="space-y-6">
        @forelse($games as $game)
            <div wire:key="game-{{ $game->id }}" class="game-card rounded-xl p-6">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="letter-circle bg-blue-600">
                                {{ strtoupper(substr($game->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $game->name }}</h3>
                                <p class="text-sm text-gray-600">
                                    Creato da: <span class="font-medium">{{ $game->creator->name }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="text-sm">
                                    <span class="font-medium">{{ $game->players->count() }}</span> / {{ $game->max_players }} giocatori
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm">
                                    @if($game->status === 'waiting')
                                        <span class="text-yellow-600 font-medium">In attesa</span>
                                    @elseif($game->status === 'started')
                                        <span class="text-green-600 font-medium">In corso</span>
                                    @else
                                        <span class="text-gray-600 font-medium">Terminata</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm">
                                    @if($game->currentRound)
                                        Round {{ $game->currentRound->round_number }} / {{ $game->settings['rounds'] ?? 5 }}
                                    @else
                                        Non iniziato
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-2">
                        @if($game->status === 'waiting' && !$game->isFull() && !$game->hasPlayer(auth()->user()))
                            <button 
                                wire:click="joinGame({{ $game->id }})"
                                class="btn-primary text-white px-4 py-2 rounded-lg font-medium text-sm"
                            >
                                Unisciti
                            </button>
                        @endif
                        
                        @if($game->hasPlayer(auth()->user()))
                            <span class="text-sm text-green-600 font-medium">Sei in questa partita</span>
                        @endif
                        
                        @if($game->isFull())
                            <span class="text-sm text-red-600 font-medium">Piena</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nessuna partita trovata</h3>
                <p class="text-gray-600 mb-4">
                    @if($filter === 'all')
                        Non ci sono partite disponibili. 
                    @else
                        Non ci sono partite che corrispondono ai tuoi criteri di ricerca. 
                    @endif
                </p>
                <a href="/games/create" class="btn-primary text-white px-6 py-2 rounded-lg font-medium inline-block">
                    Crea una nuova partita
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $games->links() }}
    </div>
</div>
