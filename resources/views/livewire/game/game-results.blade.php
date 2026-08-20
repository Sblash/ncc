<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Risultati: {{ $game['name'] ?? 'N/A' }}</h1>
                <a href="{{ route('games.index') }}" 
                   class="text-indigo-600 hover:text-indigo-900">
                    Torna alle partite
                </a>
            </div>

            @if($game['status'] ?? null === 'finished')
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-gray-800">Classifica Finale</h2>
                    @if($winner)
                        <div class="mt-4">
                            <span class="text-gray-600">Vincitore:</span>
                            <span class="text-3xl font-bold text-green-600 ml-2">{{ $winner['user']['name'] ?? 'N/A' }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-gray-800">Classifica Round {{ count($rounds) }}</h2>
                </div>
            @endif

            <div class="space-y-4">
                @foreach($sortedPlayers as $index => $player)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl font-bold text-gray-400">{{ $index + 1 }}</span>
                            <div>
                                <div class="font-medium text-lg">{{ $player['user']['name'] ?? 'N/A' }}</div>
                                @if(($player['user']['id'] ?? null) === (session('auth_user.id') ?? null))
                                    <div class="text-sm text-gray-500">(Tu)</div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold">{{ $player['score'] ?? 0 }} pts</div>
                            <div class="text-sm text-gray-500">
                                @if($game['status'] ?? null === 'finished')
                                    @if($index === 0)
                                        <span class="text-green-600 font-semibold">Vincitore</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($game['status'] ?? null === 'finished')
                <div class="mt-8 text-center">
                    <a href="{{ route('games.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Torna alle Partite
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
