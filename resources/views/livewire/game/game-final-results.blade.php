<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-900">Risultati Finali</h1>
        <p class="text-xl text-gray-600 mt-2">{{ $game->name }}</p>
        
        @if($game->creator_id === auth()->id())
            <div class="mt-4">
                <span class="bg-purple-100 text-purple-800 px-4 py-2 rounded-full text-sm font-medium">
                    Sei il creatore della partita
                </span>
            </div>
        @endif
    </div>

    <!-- Final Results Table -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Classifica Finale</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posizione</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giocatore</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Punteggio Totale</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Riconoscimenti</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($finalResults as $result)
                        <tr class="@if($result['position'] === 1) bg-yellow-50 @elseif($result['position'] <= 3) bg-gray-50 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    @if($result['position'] === 1)
                                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @elseif($result['position'] === 2)
                                        <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @elseif($result['position'] === 3)
                                        <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @else
                                        <span class="text-2xl font-bold text-gray-400">{{ $result['position'] }}°</span>
                                    @endif
                                    <span class="score-badge">{{ $result['position'] }}°</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($result['user_name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-lg text-gray-900">{{ $result['user_name'] }}</p>
                                        @if($result['user_id'] === auth()->id())
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Tu</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-3xl font-bold @if($result['position'] === 1) text-yellow-600 @elseif($result['position'] <= 3) text-gray-700 @else text-gray-500 @endif">
                                    {{ $result['score'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($result['position'] === 1)
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">
                                        🏆 Vincitore!
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Game Summary -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Riepilogo Partita</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $game->rounds->count() }}</div>
                <p class="text-gray-600">Round giocati</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $game->players->count() }}</div>
                <p class="text-gray-600">Giocatori</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $game->settings['round_duration'] ?? 60 }}s</div>
                <p class="text-gray-600">Durata round</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-3xl font-bold text-orange-600 mb-2">
                    @if($finalResults && count($finalResults) > 0)
                        {{ $finalResults[0]['score'] }}
                    @else
                        0
                    @endif
                </div>
                <p class="text-gray-600">Punteggio massimo</p>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap gap-4 justify-center">
            <button 
                wire:click="newGame"
                class="btn-primary text-white px-8 py-3 rounded-lg font-medium flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Nuova Partita</span>
            </button>
            
            <button 
                wire:click="backToGames"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-medium flex items-center space-x-2 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Torna alle partite</span>
            </button>
        </div>
    </div>
</div>
