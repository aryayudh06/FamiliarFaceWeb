<x-app-layout>
    <div class="w-full bg-[#771D1F] py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-serif text-white mb-2">Add Person for 2FA</h1>
            <p class="text-white mb-8">Record 10 seconds video of your face</p>

            <!-- Section 1: Add Person Name -->
            <div class="mb-8">
                <h2 class="text-white text-xl font-semibold mb-4">1. Add Person Name</h2>
                <div class="w-full bg-[#e0e0e0] rounded-lg p-6">
                    <form method="POST" action="{{ route('2fa.store') }}" id="add-person-form">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name"
                                class="block text-gray-700 text-sm font-bold mb-2 sr-only">Name:</label>
                            <input required id="name"
                                class="block w-full px-4 py-2 bg-white text-gray-800 rounded-md focus:outline-none focus:ring-2 focus:ring-[#a08060]"
                                type="text" name="name" value="{{ old('name') }}" required autofocus
                                placeholder="Name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600" />
                        </div>

                        <!-- The form submission button is moved to the last section -->
                    </form>
                </div>
            </div>

            <!-- Section 2: Record Person Face -->
            <div class="mb-8">
                <h2 class="text-white text-xl font-semibold mb-4">2. Record Person Face</h2>
                <p class="text-white text-sm mb-4">Please face front, slightly sideways, slightly upwards and downwards
                </p>
                <div class="w-full bg-[#e0e0e0] rounded-lg p-6 flex flex-col items-center">
                    <div id="camera-container" class="w-full aspect-video rounded-md mb-4 overflow-hidden">
                        <video id="camera-feed" class="w-full h-full object-cover" autoplay playsinline></video>
                        <canvas id="face-canvas" class="w-full h-full object-cover hidden"></canvas>
                    </div>
                    <div class="flex space-x-4">
                        <button type="button"
                            class="px-6 py-2 bg-[#771D1F] text-white text-lg font-semibold rounded-full shadow hover:bg-[#a03d3f] transition ease-in-out duration-150">
                            Record
                        </button>
                        <button type="button"
                            class="px-6 py-2 bg-[#f0e6d3] text-[#771D1F] text-lg font-semibold rounded-full shadow hover:bg-[#e0d6c3] transition ease-in-out duration-150">
                            Retry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section 3: Submit Video Result -->
            <div>
                <h2 class="text-white text-xl font-semibold mb-4">3. Submit Video Result</h2>
                <div class="w-full bg-[#e0e0e0] rounded-lg p-6 flex flex-col items-center">
                    <div id="result-container" class="w-full aspect-video rounded-md mb-4 overflow-hidden bg-gray-300">
                        <video id="recorded-result" class="w-full h-full object-cover hidden" controls></video>
                        <p id="result-placeholder"
                            class="w-full h-full flex items-center justify-center text-gray-600 text-center">Video
                            result will appear here after recording.</p>
                    </div>
                    <button type="submit" form="add-person-form"
                        class="px-6 py-2 bg-[#f0e6d3] text-[#771D1F] text-lg font-semibold rounded-full shadow hover:bg-[#e0d6c3] transition ease-in-out duration-150">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="recording-timer"
        class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white text-lg px-4 py-2 rounded-full shadow-lg z-50 hidden">
        0s / 10s</div>

    <script>
        console.log('Script tag loaded and executing...');
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM fully loaded and parsed');
            const cameraFeed = document.getElementById('camera-feed');
            const faceCanvas = document.getElementById('face-canvas');
            const recordedResult = document.getElementById('recorded-result');
            const resultPlaceholder = document.getElementById('result-placeholder');
            const recordButton = document.querySelector('#camera-container + .flex button:nth-of-type(1)');
            const retryButton = document.querySelector('#camera-container + .flex button:nth-of-type(2)');
            const recordingTimer = document.getElementById('recording-timer');
            const nameInput = document.getElementById('name');
            const submitButton = document.querySelector('#result-container + button[type="submit"]');
            const mainContent = document.querySelector('.max-w-4xl.mx-auto'); // Select the main content container

            let mediaRecorder;
            let recordedChunks = [];
            let timerInterval;
            let currentStream = null;

            // Request access to the camera
            navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                })
                .then((stream) => {
                    currentStream = stream; // Store the initial stream
                    cameraFeed.srcObject = stream;
                    recordButton.disabled = false; // Enable record button once camera is ready
                    checkSubmitButtonState(); // Check submit button state initially
                })
                .catch((error) => {
                    console.error('Error accessing camera:', error);
                    const cameraContainer = document.getElementById('camera-container');
                    cameraContainer.innerHTML =
                        '<p class="text-red-600 text-center">Could not access camera. Please ensure permissions are granted.</p>';
                    // Keep record button disabled if camera access fails
                    recordButton.disabled = true;
                    checkSubmitButtonState(); // Check submit button state on camera error
                });

            recordButton.addEventListener('click', () => {
                if (!cameraFeed.srcObject) {
                    alert('Camera feed not available.');
                    return;
                }

                recordedChunks = [];
                mediaRecorder = new MediaRecorder(cameraFeed.srcObject);

                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    const blob = new Blob(recordedChunks, {
                        type: 'video/webm'
                    }); // Or appropriate video type
                    const url = URL.createObjectURL(blob);
                    recordedResult.src = url;
                    recordedResult.classList.remove('hidden');
                    resultPlaceholder.classList.add('hidden');

                    // Hide camera feed
                    cameraFeed.classList.add('hidden');
                    // faceCanvas.classList.add('hidden'); // Keep canvas hidden for now

                    // Manage button visibility
                    recordButton.classList.add('hidden');
                    retryButton.classList.remove('hidden');
                    retryButton.disabled = false; // Enable retry button after recording finishes

                    // Hide timer
                    recordingTimer.classList.add('hidden');
                    clearInterval(timerInterval);

                    checkSubmitButtonState(); // Check submit button state after recording stops
                };

                mediaRecorder.start(1000); // Record in 1-second chunks to update timer

                // Show and start timer
                recordingTimer.classList.remove('hidden');
                let seconds = 0;
                recordingTimer.textContent = `${seconds}s / 10s`;
                timerInterval = setInterval(() => {
                    seconds++;
                    recordingTimer.textContent = `${seconds}s / 10s`;
                    if (seconds >= 10) {
                        clearInterval(timerInterval);
                        mediaRecorder.stop();
                    }
                }, 1000);

                // Disable record button while recording
                recordButton.disabled = true;
                retryButton.disabled = true; // Disable retry during recording
            });

            // Use event delegation for the retry button
            mainContent.addEventListener('click', (event) => {
                // Check if the clicked element is the retry button or a descendant of it
                if (event.target.closest('button') === retryButton) {
                    console.log('Retry button clicked via delegation');
                    // Stop the current camera stream tracks
                    if (currentStream) {
                        currentStream.getTracks().forEach(track => track.stop());
                        currentStream = null; // Clear the stored stream reference
                    }

                    // Clear recorded chunks and reset video sources
                    recordedChunks = [];
                    recordedResult.src = '';
                    recordedResult.classList.add('hidden');
                    resultPlaceholder.classList.remove('hidden');

                    // Show the camera feed element again
                    cameraFeed.classList.remove('hidden');

                    // Re-request camera access to get a fresh stream
                    navigator.mediaDevices.getUserMedia({
                            video: {
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 720
                                }
                            }
                        })
                        .then((stream) => {
                            currentStream = stream; // Store the new stream
                            cameraFeed.srcObject = stream;
                            recordButton.disabled = false; // Enable record button once camera is ready
                        })
                        .catch((error) => {
                            console.error('Error accessing camera after retry:', error);
                            const cameraContainer = document.getElementById('camera-container');
                            cameraContainer.innerHTML =
                                '<p class="text-red-600 text-center">Could not access camera. Please ensure permissions are granted.</p>';
                            recordButton.disabled =
                                true; // Keep record button disabled if camera access fails
                        });

                    // Manage button visibility
                    recordButton.classList.remove('hidden');
                    retryButton.classList.add('hidden');

                    // Hide and reset timer
                    recordingTimer.classList.add('hidden');
                    clearInterval(timerInterval);
                    recordingTimer.textContent = '0s / 10s';

                    // Ensure retry button is disabled until a new recording starts
                    retryButton.disabled = true;

                    // Check submit button state after retry
                    checkSubmitButtonState();
                }
            });

            // Function to check if submit button should be enabled
            function checkSubmitButtonState() {
                const isNameEntered = nameInput.value.trim().length > 0;
                submitButton.disabled = !isNameEntered;
            }

            // Event listener for name input
            nameInput.addEventListener('input', checkSubmitButtonState);

            // Initial state of buttons
            retryButton.classList.add('hidden');
            recordButton.disabled = true; // Disable record button initially until camera is ready
            submitButton.disabled = true; // Disable submit button initially
        });
    </script>
</x-app-layout>
