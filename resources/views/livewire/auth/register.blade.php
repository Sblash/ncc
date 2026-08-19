<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Crea il tuo account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Inizia a giocare a Nomi Cose Città
            </p>
        </div>

        <div class="mt-8 bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form wire:submit.prevent="register" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nome Utente
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="name" 
                            id="name" 
                            name="name" 
                            type="text" 
                            autocomplete="name" 
                            required
                            class="appearance-none block w-full px-3 py-2 border @error('name') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Il tuo nome"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Indirizzo Email
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="email" 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required
                            class="appearance-none block w-full px-3 py-2 border @error('email') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="nome@esempio.it"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="password" 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="new-password" 
                            required
                            class="appearance-none block w-full px-3 py-2 border @error('password') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Minimo 8 caratteri"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Conferma Password
                    </label>
                    <div class="mt-1">
                        <input 
                            wire:model="password_confirmation" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            type="password" 
                            autocomplete="new-password" 
                            required
                            class="appearance-none block w-full px-3 py-2 border @error('password_confirmation') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Conferma la password"
                        >
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Registrati
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Hai già un account?
                    <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                        Accedi
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
