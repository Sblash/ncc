<div class="max-w-6xl mx-auto" wire:poll.5s="checkRoundStatus">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Votazione Round {{ $round->round_number }}</h1>
        <p class="text-gray-600 mt-1">Vota le parole degli altri giocatori</p>
    </div>

    <!-- Voting Instructions -->
    <div class="bg-blue-50 rounded-lg p-4 mb-8">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-blue-800">
                <strong>Istruzioni:</strong> Vota se ogni parola è valida secondo le regole del gioco.
                Una parola è valida se inizia con la lettera <strong>{{ $round->letter }}</strong> e appartiene alla categoria corretta.
            </p>
        </div>
    </div>

    <!-- Words to Vote -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Parole da votare</h2>
        
        @if(count($words) > 0)
            <div class="space-y-6">
                @foreach($words as $word)
                    <div wire:key="word-{{ $word['id'] }}" class="word-card bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($word['user']['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $word['user']['name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $word['category']['name'] }}</p>
                                    </div>
                                </div>
                                <p class="text-2xl font-bold text-gray-900">{{ $word['word'] }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    wire:click="vote({{ $word['id'] }}, true)"
                                    class="@if($votes[$word['id']] === true) bg-green-600 text-white @else bg-gray-200 text-gray-700 hover:bg-green-100 @endif px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Valida</span>
                                </button>
                                <button 
                                    wire:click="vote({{ $word['id'] }}, false)"
                                    class="@if($votes[$word['id']] === false) bg-red-600 text-white @else bg-gray-200 text-gray-700 hover:bg-red-100 @endif px-6 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span>Non valida</span>
                                </button>
                            </div>
                        </div>
                        @if($votes[$word['id']] !== null)
                            <div class="mt-3 p-2 bg-blue-100 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    @if($votes[$word['id']] === true)
                                        Hai votato: <strong>Valida</strong>
                                    @else
                                        Hai votato: <strong>Non valida</strong>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600">Non ci sono parole da votare al momento.</p>
            </div>
        @endif
    </div>

    <!-- Voting Status -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Stato della votazione</h2>
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">
                    @if($allVoted)
                        <span class="text-green-600 font-medium">Hai votato tutte le parole!</span>
                    @else
                        <span class="text-yellow-600 font-medium">Ci sono ancora parole da votare</span>
                    @endif
                </p>
            </div>
            
            @if($isCreator)
                <button 
                    wire:click="completeRound"
                    class="btn-success text-white px-6 py-3 rounded-lg font-medium flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Completa Round</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Game Info -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Informazioni partita</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-2xl font-bold">{{ $game->players->count() }}</span>
                </div>
                <p class="text-sm text-gray-600">Giocatori</p>
            </div>
            <div class="text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span class="text-2xl font-bold">{{ $round->round_number }}</span>
                </div>
                <p class="text-sm text-gray-600">Round corrente</p>
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
</div>
