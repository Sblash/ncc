<div class="py-12" wire:poll.5s="loadRound">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Votazione Round</h1>
                <a href="{{ route('games.index') }}" 
                   class="text-indigo-600 hover:text-indigo-900">
                    Torna alle partite
                </a>
            </div>

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700">
                    Round: {{ $round['category'] ?? 'N/A' }} (Lettera: {{ $round['letter'] ?? 'A' }})
                </h2>
                <p class="text-gray-600 mt-2">
                    Vota se le parole degli altri giocatori sono valide
                </p>
            </div>

            <div class="space-y-6">
                @forelse($wordsToVote as $word)
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <span class="font-medium">{{ $word['user']['name'] ?? 'N/A' }}</span>
                                <span class="text-sm text-gray-500">({{ $word['category']['name'] ?? 'N/A' }})</span>
                            </div>
                            <span class="font-bold text-lg">{{ $word['word'] ?? 'N/A' }}</span>
                        </div>

                        <div class="flex space-x-4">
                            <button wire:click="vote({{ $word['id'] }}, true)"
                                    wire:loading.attr="disabled"
                                    :disabled="isset($votes[$word['id']])"
                                    class="flex-1 py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                @if(isset($votes[$word['id']]) && $votes[$word['id']])
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    Valida
                                @endif
                            </button>
                            
                            <button wire:click="vote({{ $word['id'] }}, false)"
                                    wire:loading.attr="disabled"
                                    :disabled="isset($votes[$word['id']])"
                                    class="flex-1 py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                @if(isset($votes[$word['id']]) && !$votes[$word['id']])
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    Non Valida
                                @endif
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500">Non ci sono parole da votare</p>
                    </div>
                @endforelse
            </div>

            @if($hasVotedAll)
                <div class="mt-8 text-center">
                    <p class="text-gray-600 mb-4">Hai completato la votazione. Aspetta che tutti votino...</p>
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                </div>
            @endif
        </div>
    </div>
</div>
