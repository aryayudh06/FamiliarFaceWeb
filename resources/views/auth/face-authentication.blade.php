<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <h2 class="text-2xl font-bold text-center mb-6">Face Authentication</h2>

            <div class="relative">
                <video id="video" class="w-full rounded-lg" autoplay playsinline></video>
                <canvas id="canvas" class="hidden"></canvas>

                <div id="status" class="mt-4 text-center text-gray-600">
                    Please look at the camera
                </div>

                <div class="mt-4 flex justify-center">
                    <button id="capture" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Capture
                    </button>
                </div>
            </div>

            <div id="error" class="mt-4 text-center text-red-600 hidden"></div>
            <div id="debug" class="mt-4 text-center text-gray-600 text-sm"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const captureButton = document.getElementById('capture');
            const statusDiv = document.getElementById('status');
            const errorDiv = document.getElementById('error');
            const debugDiv = document.getElementById('debug');

            let stream = null;
            let ws = null;

            // Debug function
            function debug(message) {
                console.log(message);
                debugDiv.textContent += message + '\n';
            }

            // Initialize camera
            async function initCamera() {
                try {
                    debug('Initializing camera...');
                    const constraints = {
                        video: {
                            facingMode: 'user',
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        }
                    };

                    debug('Requesting camera access...');
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    debug('Camera access granted');

                    video.srcObject = stream;
                    await video.play();
                    debug('Video playback started');

                    // Check if video is actually playing
                    video.addEventListener('playing', () => {
                        debug('Video is playing');
                    });

                    video.addEventListener('error', (e) => {
                        debug('Video error: ' + e.message);
                        errorDiv.textContent = 'Video error: ' + e.message;
                        errorDiv.classList.remove('hidden');
                    });

                } catch (err) {
                    debug('Camera error: ' + err.message);
                    errorDiv.textContent = 'Error accessing camera: ' + err.message;
                    errorDiv.classList.remove('hidden');
                }
            }

            // Initialize WebSocket connection
            function initWebSocket() {
                try {
                    debug('Initializing WebSocket...');
                    ws = new WebSocket('ws://localhost:5000/ws/authenticate');

                    ws.onopen = () => {
                        debug('WebSocket connected');
                    };

                    ws.onmessage = function(event) {
                        debug('WebSocket message received');
                        const data = JSON.parse(event.data);
                        if (data.status === 'success' && data.results.length > 0) {
                            const result = data.results[0];
                            if (result.confidence > 0.75) {
                                statusDiv.textContent = 'Face detected! Click capture to authenticate.';
                                statusDiv.classList.remove('text-red-600');
                                statusDiv.classList.add('text-green-600');
                            }
                        }
                    };

                    ws.onerror = function(error) {
                        debug('WebSocket error: ' + error);
                        errorDiv.textContent = 'Connection error. Please try again.';
                        errorDiv.classList.remove('hidden');
                    };

                    ws.onclose = function() {
                        debug('WebSocket closed');
                    };
                } catch (err) {
                    debug('WebSocket initialization error: ' + err.message);
                    errorDiv.textContent = 'WebSocket error: ' + err.message;
                    errorDiv.classList.remove('hidden');
                }
            }

            // Capture and send image
            async function captureAndSend() {
                try {
                    debug('Capturing image...');
                    const context = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    debug('Image captured');

                    const imageData = canvas.toDataURL('image/jpeg');
                    debug('Image converted to base64');

                    debug('Sending image to server...');
                    const response = await fetch('/face/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            image: imageData
                        })
                    });

                    const data = await response.json();
                    debug('Server response received: ' + JSON.stringify(data));

                    if (data.status === 'success') {
                        window.location.href = '/dashboard';
                    } else {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('hidden');
                    }
                } catch (err) {
                    debug('Capture error: ' + err.message);
                    errorDiv.textContent = 'Error during authentication: ' + err.message;
                    errorDiv.classList.remove('hidden');
                }
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', () => {
                debug('Page loaded, initializing...');
                initCamera();
                initWebSocket();

                captureButton.addEventListener('click', captureAndSend);
            });

            // Cleanup
            window.addEventListener('beforeunload', () => {
                debug('Cleaning up...');
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    debug('Camera stream stopped');
                }
                if (ws) {
                    ws.close();
                    debug('WebSocket closed');
                }
            });
        </script>
    @endpush
</x-guest-layout>
