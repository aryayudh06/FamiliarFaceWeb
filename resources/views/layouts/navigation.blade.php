<header x-data="{ open: false }" class="w-full flex justify-between items-center px-6 py-4 bg-[#771D1F]">
    <div class="flex items-center">
        <h1 class="text-3xl font-serif text-white mr-8">FamiliarFace</h1>
        <a href="{{ route('dashboard') }}" class="text-white text-lg relative pb-1">
            Dashboard
            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-white"></span>
        </a>
    </div>
    <div class="relative">
        <button @click="open = ! open" class="flex items-center text-white focus:outline-none">
            <span class="mr-2">{{ Auth::user()->name }}</span>
            <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
        </button>

        <!-- Dropdown menu -->
        <div x-show="open"
            @click.outside="open = false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
            style="display: none;">

            @if (session('face_authenticated'))
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Profile
                </a>
            @endif

            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</header>
