<div class="max-w-6xl mx-auto" wire:poll.5s="checkRoundStatus">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Risultati Round {{ $round->round_number }}</h1>
        <p class="text-gray-600 mt-1">Lettera: <span class="text-2xl font-bold text-blue-600">{{ $round->letter }}</span></p>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Classifica Round</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posizione</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giocatore</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Punteggio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parole</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($results as $index => $result)
                        <tr class="@if($index % 2 === 0) bg-gray-50 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="score-badge">{{ $index + 1 }}°</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($result['user_name'], 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $result['user_name'] }}</span>
                                    @if($result['user_id'] === auth()->id())
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Tu</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xl font-bold @if($result['score'] > 0) text-green-600 @else text-red-600 @endif">
                                    {{ $result['score'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    @foreach($result['words'] as $word)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm @if($word['score'] > 0) text-green-600 @else text-red-600 @endif">
                                                {{ $word['word'] }} ({{ $word['category'] }})
                                            </span>
                                            <span class="text-xs @if($word['score'] > 0) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif px-2 py-1 rounded-full">
                                                @if($word['score'] > 0)
                                                    +{{ $word['score'] }}
                                                @else
                                                    {{ $word['score'] }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Next Round Button -->
    @if($showNextRoundButton)
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="flex justify-end">
                <button 
                    wire:click="nextRound"
                    class="btn-success text-white px-8 py-3 rounded-lg font-medium flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 12h14"></path>
                    </svg>
                    <span>Prossimo Round</span>
                </button>
            </div>
        </div>
    @endif

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
