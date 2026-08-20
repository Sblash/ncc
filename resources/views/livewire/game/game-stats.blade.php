<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Statistiche</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Le tue statistiche</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Partite giocate:</span>
                            <span class="font-semibold">{{ $stats['stats']['games_played'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Punteggio totale:</span>
                            <span class="font-semibold">{{ $stats['stats']['total_score'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Punteggio medio:</span>
                            <span class="font-semibold">{{ $stats['stats']['avg_score'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Partite vinte:</span>
                            <span class="font-semibold">{{ $stats['stats']['games_won'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Percentuale vittorie:</span>
                            <span class="font-semibold">{{ $stats['stats']['win_rate'] ?? 0 }}%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Classifica Globale</h2>
                    <div class="space-y-3">
                        @foreach($leaderboard as $index => $entry)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-bold text-gray-400">{{ $index + 1 }}</span>
                                    <span>{{ $entry['user']['name'] ?? 'N/A' }}</span>
                                </div>
                                <span class="font-semibold">{{ $entry['total_score'] ?? 0 }} pts</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
