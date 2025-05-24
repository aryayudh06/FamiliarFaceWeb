<x-app-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">Registered 2FA Users</h1>

        <div class="w-full bg-[#e0e0e0] rounded-lg p-6 mb-6">
            @if ($register2FAs->count() > 0)
                <ul class="divide-y divide-gray-300">
                    @foreach ($register2FAs as $register2FA)
                        <li class="flex justify-between items-center py-3 hover:bg-gray-200 px-2 rounded-md group">
                            <span class="text-gray-800 text-lg">{{ $register2FA->name }}</span>
                            <form action="{{ route('2fa.destroy', $register2FA) }}" method="POST"
                                class="inline-block opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    Delete
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600 text-center">No 2FA users registered yet.</p>
            @endif
        </div>

        <a href="{{ route('2fa.create') }}"
            class="px-6 py-2 bg-white text-[#771D1F] text-lg font-semibold rounded-full shadow hover:bg-gray-200 transition ease-in-out duration-150">
            Add New Person
        </a>
    </div>
</x-app-layout>
