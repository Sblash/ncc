<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Crea una nuova partita</h1>
        <p class="text-gray-600 mt-1">Personalizza le impostazioni della tua partita</p>
    </div>

    <form wire:submit.prevent="createGame" class="space-y-8">
        <!-- Game Name -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Informazioni di base</h2>
            
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nome della partita <span class="text-red-500">*</span>
                </label>
                <input 
                    wire:model="name" 
                    type="text" 
                    id="name" 
                    placeholder="Es: Partita del venerdì sera"
                    class="w-full px-4 py-3 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Players and Rounds -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="max_players" class="block text-sm font-medium text-gray-700 mb-2">
                        Numero massimo di giocatori <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model="max_players" 
                        type="number" 
                        id="max_players" 
                        min="2" 
                        max="20"
                        class="w-full px-4 py-3 border @error('max_players') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('max_players')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rounds" class="block text-sm font-medium text-gray-700 mb-2">
                        Numero di round <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model="rounds" 
                        type="number" 
                        id="rounds" 
                        min="1" 
                        max="20"
                        class="w-full px-4 py-3 border @error('rounds') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('rounds')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="round_duration" class="block text-sm font-medium text-gray-700 mb-2">
                        Durata del round (secondi) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model="round_duration" 
                        type="number" 
                        id="round_duration" 
                        min="30" 
                        max="300"
                        class="w-full px-4 py-3 border @error('round_duration') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    @error('round_duration')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Letters Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Lettere da usare</h2>
            <p class="text-gray-600 mb-4">Seleziona le lettere che verranno usate nei round</p>
            
            @error('selectedLetters')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap gap-2">
                @foreach($availableLetters as $letter)
                    <button 
                        type="button"
                        wire:click="toggleLetter('{{ $letter }}')"
                        class="px-4 py-2 rounded-lg font-medium transition-colors @if(in_array($letter, $selectedLetters)) bg-blue-600 text-white @else bg-gray-200 text-gray-700 hover:bg-gray-300 @endif"
                    >
                        {{ $letter }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Categories Selection -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Categorie</h2>
            <p class="text-gray-600 mb-4">Seleziona le categorie che verranno usate nei round</p>
            
            @error('selectedCategories')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($availableCategories as $category)
                    <label class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors">
                        <input 
                            type="checkbox" 
                            wire:model="selectedCategories"
                            value="{{ $category['id'] }}"
                            class="h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                        >
                        <span class="text-gray-700 font-medium">{{ $category['name'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="btn-primary text-white px-8 py-3 rounded-lg font-medium flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Crea Partita</span>
                </button>
            </div>
        </div>
    </form>
</div>
