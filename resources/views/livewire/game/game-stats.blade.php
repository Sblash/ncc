<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Le tue Statistiche</h1>
        <p class="text-gray-600 mt-1">Dati e classifiche personali</p>
    </div>

    <!-- User Stats -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Le tue statistiche</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['games_played'] }}</div>
                <p class="text-gray-600">Partite giocate</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg">
                <svg class="w-16 h-16 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $stats['total_score'] }}</div>
                <p class="text-gray-600">Punteggio totale</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg">
                <svg class="w-16 h-16 text-purple-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $stats['avg_score'] }}</div>
                <p class="text-gray-600">Media punteggio</p>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg">
                <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <div class="text-3xl font-bold text-yellow-600 mb-2">{{ $stats['wins'] }}</div>
                <p class="text-gray-600">Vittorie</p>
            </div>
        </div>
    </div>

    <!-- Recent Games -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Partite recenti</h2>
        
        @if(count($recentGames) > 0)
            <div class="space-y-4">
                @foreach($recentGames as $game)
                    <div wire:key="recent-game-{{ $game->id }}" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="letter-circle bg-blue-600">
                                {{ strtoupper(substr($game->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ $game->name }}</h3>
                                <p class="text-sm text-gray-600">
                                    Creato da: {{ $game->creator->name }} • 
                                    {{ $game->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <p class="font-bold text-gray-900">{{ $game->players->count() }}</p>
                                <p class="text-xs text-gray-600">Giocatori</p>
                            </div>
                            <div class="text-center">
                                <p class="font-bold @if($game->status === 'waiting') text-yellow-600 @elseif($game->status === 'started') text-green-600 @else text-gray-600 @endif">
                                    @if($game->status === 'waiting') In attesa @elseif($game->status === 'started') In corso @else Terminata @endif
                                </p>
                                <p class="text-xs text-gray-600">Stato</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600">Non hai ancora giocato nessuna partita</p>
            </div>
        @endif
    </div>

    <!-- Leaderboard -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Classifica Globale</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posizione</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giocatore</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Punteggio Totale</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partite Giocate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Media</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vittorie</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($leaderboard as $entry)
                        <tr class="@if($entry['position'] === 1) bg-yellow-50 @elseif($entry['position'] <= 3) bg-gray-50 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="score-badge">{{ $entry['position'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($entry['name'], 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $entry['name'] }}</span>
                                    @if($entry['user_id'] === auth()->id())
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Tu</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-gray-900">{{ $entry['total_score'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-600">{{ $entry['games_played'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-600">{{ $entry['avg_score'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-600">{{ $entry['wins'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
