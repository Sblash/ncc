<div class="py-12" wire:poll.5s="loadGame" x-data="{
    timer: null,
    timeLeft: @entangle('timeRemaining'),
    startTimer() {
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            }
        }, 1000);
    }
}" x-init="startTimer()" x-on:round-updated.window="timeLeft = $event.detail.timeRemaining; startTimer();">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Round: {{ $round['category'] ?? 'N/A' }}</h1>
                <div class="text-right">
                    <div class="text-2xl font-bold @if($timeRemaining <= 10) text-red-600 @else text-gray-900 @endif">
                        <span x-text="Math.floor(timeLeft / 60)"></span>:<span x-text="String(timeLeft % 60).padStart(2, '0')"></span>
                    </div>
                    <div class="text-sm text-gray-500">Tempo rimanente</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">
                        Lettera: <span class="text-3xl font-bold text-indigo-600">{{ $round['letter'] ?? 'A' }}</span>
                    </h2>
                    <p class="text-gray-600 mb-4">
                        Inserisci parole che iniziano con la lettera "{{ $round['letter'] ?? 'A' }}" 
                        per la categoria "{{ $round['category'] ?? 'N/A' }}"
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block font-medium text-sm text-gray-700 mb-2">
                                Seleziona Categoria
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($categories as $category)
                                    <button wire:click="selectCategory({{ $category['id'] }})"
                                            class="px-4 py-2 text-sm border rounded-lg @if($selectedCategoryId === $category['id']) bg-indigo-600 text-white border-indigo-600 @else border-gray-300 hover:border-indigo-500 @endif">
                                        {{ $category['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @if($selectedCategoryId)
                            <div>
                                <label for="wordInput" class="block font-medium text-sm text-gray-700">
                                    Inserisci Parola
                                </label>
                                <div class="flex space-x-2 mt-2">
                                    <input id="wordInput" type="text" wire:model="wordInput"
                                           wire:keydown.enter="submitWord"
                                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <button wire:click="submitWord"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        Invia
                                    </button>
                                </div>
                                @error('wordInput') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Le tue parole</h2>
                    <div class="space-y-2">
                        @forelse($userWords as $word)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="font-medium">{{ $word['word'] ?? 'N/A' }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $word['category']['name'] ?? 'N/A' }})</span>
                                </div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    In attesa
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Non hai ancora inserito parole</p>
                        @endforelse
                    </div>

                    <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-8">Classifica Parziale</h2>
                    <div class="space-y-2">
                        @foreach($game['players'] ?? [] as $player)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium">{{ $player['user']['name'] ?? 'N/A' }}</span>
                                    @if(($player['user']['id'] ?? null) === (session('auth_user.id') ?? null))
                                        <span class="text-xs text-gray-500">(Tu)</span>
                                    @endif
                                </div>
                                <span class="font-bold">{{ $player['score'] ?? 0 }} pts</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
