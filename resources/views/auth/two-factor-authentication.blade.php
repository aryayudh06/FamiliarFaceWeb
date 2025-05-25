<x-app-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">Registered 2FA Users</h1>

        @if (session('success'))
            <div class="w-full mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="w-full mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="w-full bg-[#e0e0e0] rounded-lg p-6 mb-6">
            @if ($register2FAs->count() > 0)
                <ul class="divide-y divide-gray-300">
                    @foreach ($register2FAs as $register2FA)
                        <li class="flex justify-between items-center py-3 hover:bg-gray-200 px-2 rounded-md group">
                            <span class="text-gray-800 text-lg">{{ $register2FA->name }}</span>
                            <form id="delete-form-{{ $register2FA->id }}"
                                action="{{ route('2fa.destroy', $register2FA) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="text-red-600 hover:text-red-800 text-sm"
                                    data-modal-target="confirm-delete-modal" data-modal-toggle="confirm-delete-modal"
                                    data-user-id="{{ $register2FA->id }}" data-user-name="{{ $register2FA->name }}">
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

    <!-- Confirmation Modal -->
    <div id="confirm-delete-modal" tabindex="-1"
        class="fixed top-0 right-0 left-0 z-50 hidden h-modal md:h-full md:inset-0 items-center justify-center">
        <div class="relative p-4 w-full max-w-md h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <button type="button"
                    class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-800 dark:hover:text-white"
                    data-modal-hide="confirm-delete-modal">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-6 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to
                        delete <span id="user-name-placeholder" class="font-semibold"></span>?</h3>
                    <button id="confirm-delete-button" type="button"
                        class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                        Yes, I'm sure
                    </button>
                    <button data-modal-hide="confirm-delete-modal" type="button"
                        class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No,
                        cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('confirm-delete-modal');
        const deleteButtons = document.querySelectorAll('[data-modal-target="confirm-delete-modal"]');
        const confirmDeleteButton = document.getElementById('confirm-delete-button');
        const userNamePlaceholder = document.getElementById('user-name-placeholder');
        let userIdToDelete = null;

        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                userIdToDelete = button.dataset.userId;
                const userName = button.dataset.userName;
                userNamePlaceholder.textContent = userName;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('role', 'dialog');
                modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Add backdrop effect
            });
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (userIdToDelete) {
                const form = document.getElementById(`delete-form-${userIdToDelete}`);
                form.submit();
            }
            // Hide modal after submission attempt
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
            modal.style.backgroundColor = ''; // Remove backdrop effect
            userIdToDelete = null; // Reset user ID
        });

        // Close modal when clicking outside or on the close button
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('[data-modal-hide="confirm-delete-modal"]')) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.removeAttribute('aria-modal');
                modal.removeAttribute('role');
                modal.style.backgroundColor = ''; // Remove backdrop effect
                userIdToDelete = null; // Reset user ID
            }
        });
    </script>
</x-app-layout>
