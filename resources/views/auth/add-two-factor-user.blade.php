<x-app-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-md mx-auto px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">Add New 2FA User</h1>

        <div class="w-full bg-[#e0e0e0] rounded-lg p-6">
            <form method="POST" action="{{ route('2fa.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                    <input id="name"
                        class="block w-full px-4 py-2 bg-white text-gray-800 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                        type="text" name="name" value="{{ old('name') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <button type="submit"
                        class="px-6 py-2 bg-[#771D1F] text-white text-lg font-semibold rounded-full shadow hover:bg-[#a03d3f] transition ease-in-out duration-150">
                        Add Person
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
