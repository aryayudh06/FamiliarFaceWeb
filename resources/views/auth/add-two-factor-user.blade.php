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
                    <button type="button" id="submitBtn"
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
            const submitButton = document.getElementById('submitBtn');
            const mainContent = document.querySelector('.max-w-4xl.mx-auto');

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
                    currentStream = stream;
                    cameraFeed.srcObject = stream;
                    recordButton.disabled = false;
                    checkSubmitButtonState();
                })
                .catch((error) => {
                    console.error('Error accessing camera:', error);
                    const cameraContainer = document.getElementById('camera-container');
                    cameraContainer.innerHTML =
                        '<p class="text-red-600 text-center">Could not access camera. Please ensure permissions are granted.</p>';
                    recordButton.disabled = true;
                    checkSubmitButtonState();
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
                    });
                    const url = URL.createObjectURL(blob);
                    recordedResult.src = url;
                    recordedResult.classList.remove('hidden');
                    resultPlaceholder.classList.add('hidden');

                    cameraFeed.classList.add('hidden');

                    recordButton.classList.add('hidden');
                    retryButton.classList.remove('hidden');
                    retryButton.disabled = false;

                    recordingTimer.classList.add('hidden');
                    clearInterval(timerInterval);

                    checkSubmitButtonState();
                };

                mediaRecorder.start(1000);

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

                recordButton.disabled = true;
                retryButton.disabled = true;
            });

            // Submit button click handler
            submitButton.addEventListener('click', async () => {
                if (!recordedChunks.length) {
                    alert('Please record a video first.');
                    return;
                }

                const name = nameInput.value.trim();
                if (!name) {
                    alert('Please enter a name.');
                    return;
                }

                try {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Submitting...';

                    const videoBlob = new Blob(recordedChunks, {
                        type: 'video/webm'
                    });
                    const formData = new FormData();
                    formData.append('email', '{{ Auth::user()->email }}');
                    formData.append('personName', name);
                    formData.append('video', videoBlob, 'face_recording.webm');

                    const response = await fetch('http://localhost:5000/register-face', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        // Create the 2FA record in Laravel
                        const laravelResponse = await fetch('{{ route('2fa.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                name: name
                            })
                        });

                        if (laravelResponse.ok) {
                            window.location.href = '{{ route('2fa.index') }}';
                        } else {
                            throw new Error('Failed to create 2FA record');
                        }
                    } else {
                        throw new Error(result.message || 'Face registration failed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit';
                }
            });

            // Use event delegation for the retry button
            mainContent.addEventListener('click', (event) => {
                if (event.target.closest('button') === retryButton) {
                    console.log('Retry button clicked via delegation');
                    if (currentStream) {
                        currentStream.getTracks().forEach(track => track.stop());
                        currentStream = null;
                    }

                    recordedChunks = [];
                    recordedResult.src = '';
                    recordedResult.classList.add('hidden');
                    resultPlaceholder.classList.remove('hidden');

                    cameraFeed.classList.remove('hidden');

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
                            currentStream = stream;
                            cameraFeed.srcObject = stream;
                            recordButton.disabled = false;
                        })
                        .catch((error) => {
                            console.error('Error accessing camera after retry:', error);
                            const cameraContainer = document.getElementById('camera-container');
                            cameraContainer.innerHTML =
                                '<p class="text-red-600 text-center">Could not access camera. Please ensure permissions are granted.</p>';
                            recordButton.disabled = true;
                        });

                    recordButton.classList.remove('hidden');
                    retryButton.classList.add('hidden');

                    recordingTimer.classList.add('hidden');
                    clearInterval(timerInterval);
                    recordingTimer.textContent = '0s / 10s';

                    retryButton.disabled = true;

                    checkSubmitButtonState();
                }
            });

            function checkSubmitButtonState() {
                const isNameEntered = nameInput.value.trim().length > 0;
                const hasRecording = recordedChunks.length > 0;
                submitButton.disabled = !(isNameEntered && hasRecording);
            }

            nameInput.addEventListener('input', checkSubmitButtonState);

            retryButton.classList.add('hidden');
            recordButton.disabled = true;
            submitButton.disabled = true;
        });
    </script>
</x-app-layout>
