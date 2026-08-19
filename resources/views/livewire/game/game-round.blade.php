<div class="max-w-6xl mx-auto" wire:poll.5s="checkRoundStatus">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Round {{ $round->round_number }}</h1>
                <p class="text-gray-600 mt-1">
                    Lettera: <span class="text-4xl font-bold text-blue-600">{{ $round->letter }}</span>
                </p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-gray-900" x-data="{ time: {{ $timeRemaining }} }" x-init="
                    setInterval(() => {
                        if (time > 0) {
                            time--;
                        } else {
                            // Force refresh
                            @this.call('checkRoundStatus');
                        }
                    }, 1000);
                " x-text="time + 's'">
                </div>
                <p class="text-sm text-gray-600">Tempo rimanente</p>
                <div class="mt-2 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div 
                        class="timer-bar h-full"
                        style="width: calc(({{ $timeRemaining }} / {{ $game->getRoundDuration() / 1000 }}) * 100%)"
                        x-transition:enter="transition-all duration-1000"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories and Word Input -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Inserisci le tue parole</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $categoryId)
                @php
                    $category = \App\Models\Category::find($categoryId);
                @endphp
                @if($category)
                    <div class="space-y-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h3 class="font-bold text-blue-800 text-center mb-2">{{ $category->name }}</h3>
                            <input 
                                wire:model.defer="selectedWords.{{ $categoryId }}"
                                wire:change="submitWord({{ $categoryId }})"
                                type="text"
                                placeholder="Inserisci una parola che inizia con {{ $round->letter }}..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @if(!empty($selectedWords[$categoryId] ?? ''))
                                <div class="mt-2 flex justify-between items-center">
                                    <span class="text-sm text-green-600">Parola salvata!</span>
                                    <button 
                                        wire:click="submitWord({{ $categoryId }})"
                                        class="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        Salva
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button 
                wire:click="submitAllWords"
                wire:loading.attr="disabled"
                class="btn-primary text-white px-6 py-3 rounded-lg font-medium flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Salva tutte le parole</span>
            </button>
        </div>
    </div>

    <!-- Game Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
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

    <!-- Players List -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Giocatori</h2>
        
        <div class="space-y-4">
            @foreach($game->players as $player)
                <div wire:key="player-{{ $player->id }}" class="player-card flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($player->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $player->user->name }}</p>
                            <p class="text-sm text-gray-600">Punteggio: <span class="font-bold">{{ $player->score }}</span></p>
                        </div>
                    </div>
                    @if($player->user->id === auth()->id())
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Tu</span>
                    @endif
                    @if($game->creator_id === $player->user->id)
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Creatore</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
