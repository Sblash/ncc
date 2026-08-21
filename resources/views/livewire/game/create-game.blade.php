<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Crea Nuova Partita</h1>

            <form wire:submit="submit" class="space-y-6">
                <div>
                    <label for="name" class="block font-medium text-sm text-gray-700">
                        Nome Partita
                    </label>
                    <input id="name" type="text" wire:model="name"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="maxPlayers" class="block font-medium text-sm text-gray-700">
                        Massimo Giocatori (2-20)
                    </label>
                    <input id="maxPlayers" type="number" wire:model="maxPlayers" min="2" max="20"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('maxPlayers') border-red-500 @enderror">
                    @error('maxPlayers') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">
                        Categorias
                    </label>
                    <div class="mt-2 space-y-2">
                        @foreach($allCategories as $category)
                            <div class="flex items-center">
                                <input id="category-{{ $category['id'] }}" type="checkbox" 
                                       wire:model="selectedCategories" 
                                       value="{{ $category['id'] }}"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="category-{{ $category['id'] }}" class="ml-2 block text-sm text-gray-900">
                                    {{ $category['name'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedCategories') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="rounds" class="block font-medium text-sm text-gray-700">
                        Numero di Round (1-10)
                    </label>
                    <input id="rounds" type="number" wire:model="rounds" min="1" max="10"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('rounds') border-red-500 @enderror">
                    @error('rounds') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="roundDuration" class="block font-medium text-sm text-gray-700">
                        Durata Round (secondi, 30-300)
                    </label>
                    <input id="roundDuration" type="number" wire:model="roundDuration" min="30" max="300"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('roundDuration') border-red-500 @enderror">
                    @error('roundDuration') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" wire:click="cancel"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Annulla
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Crea Partita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
