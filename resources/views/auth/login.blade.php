<x-guest-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-md px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">FamiliarFace</h1>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-yellow-200" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="w-full">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <input id="email"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="Email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-white" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <input id="password"
                    class="block w-full px-4 py-2 bg-[#e0e0e0] text-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                    type="password" name="password" required autocomplete="current-password" placeholder="Password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-white" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center text-white">
                    <input id="remember_me" type="checkbox"
                        class="rounded border-gray-300 text-[#a08060] shadow-sm focus:ring-[#a08060]" name="remember">
                    <span class="ms-2 text-sm">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex flex-col items-center justify-center mt-6">
                <button type="submit"
                    class="w-full px-6 py-2 text-white bg-[#a08060] hover:bg-[#b09070] rounded-full text-lg font-semibold transition ease-in-out duration-150">
                    {{ __('Login') }}
                </button>

                @if (Route::has('password.request'))
                    <a class="underline text-sm text-white hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#a08060] mt-4"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
        </form>
    </div>
</x-guest-layout>
