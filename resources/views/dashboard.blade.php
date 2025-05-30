<x-app-layout>
    <div class="flex flex-col items-center justify-center min-h-screen -mt-[64px]">
        <h2 class="text-5xl font-serif text-white mb-2">Enable 2FA now</h2>
        <p class="text-white text-opacity-80 mb-8">
            @if (auth()->user()->has_active_2_f_a)
                2FA Active
            @else
                2FA Currently Inactive
            @endif
        </p>
        <button
            class="px-10 py-3 bg-white text-[#771D1F] text-xl font-semibold rounded-lg shadow hover:bg-gray-200 transition ease-in-out duration-150"
            onclick="window.location.href='{{ route('2fa.index') }}'">
            Manage 2FA
        </button>
    </div>
</x-app-layout>
