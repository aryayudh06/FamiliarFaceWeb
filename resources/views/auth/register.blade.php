<x-guest-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-md px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">FamiliarFace</h1>

        <form method="POST" action="{{ route('register') }}" class="w-full">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <input id="name"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    placeholder="Name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-white" />
            </div>

            <!-- Email Address -->
            <div class="mb-4">
                <input id="email"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                    placeholder="Email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-white" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <input id="password"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="password" name="password" required autocomplete="new-password" placeholder="Password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-white" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <input id="password_confirmation"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="password" name="password_confirmation" required autocomplete="new-password"
                    placeholder="Confirm Password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-white" />
            </div>

            <div class="flex flex-col items-center justify-center">
                <button type="submit"
                    class="w-full px-6 py-2 text-white bg-[#a08060] hover:bg-[#b09070] rounded-full text-lg font-semibold transition ease-in-out duration-150">
                    {{ __('Register') }}
                </button>

                <a class="underline text-sm text-white hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#a08060] mt-4"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
