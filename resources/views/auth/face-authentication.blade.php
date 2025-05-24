<x-guest-layout>
    <div class="flex flex-col items-center justify-center w-full max-w-2xl px-4 py-8">
        <h1 class="text-5xl font-serif text-white mb-8">FamiliarFace</h1>

        <!-- Camera/Canvas Placeholder -->
        <div class="w-full bg-[#e0e0e0] rounded-lg p-4 mb-6 flex items-center justify-center">
            <div id="video-container" class="relative w-full aspect-video rounded-lg overflow-hidden">
                <video id="video" class="w-full h-full object-cover" autoplay playsinline></video>
                <canvas id="canvas" class="absolute top-0 left-0 w-full h-full"></canvas>
            </div>
        </div>

        <!-- Debug Log Placeholder -->
        <div class="w-full bg-[#e0e0e0] rounded-lg p-4">
            <h3 class="font-sans text-gray-800 mb-2">Log :</h3>
            <div id="status" class="mt-2 p-2 rounded-lg text-center text-sm connecting">Connecting...</div>
            <div id="error" class="mt-2 text-center text-red-600 text-sm hidden"></div>
            <div id="debugLog" class="text-sm font-mono text-gray-700 h-40 overflow-y-auto"></div>
        </div>

        <div class="mt-6 flex justify-center space-x-4">
            <button type="button" id="startBtn"
                class="px-6 py-2 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-full transition ease-in-out duration-150">
                Start Camera
            </button>
            <button type="button" id="stopBtn"
                class="px-6 py-2 bg-red-500 hover:bg-red-700 text-white font-bold rounded-full transition ease-in-out duration-150"
                disabled>
                Stop Camera
            </button>
        </div>
    </div>

    <script>
        // Global variables
        let stream = null;
        let socket = null;
        let processing = false;
        let frameInterval = null;
        const TARGET_FPS = 5;

        // Get DOM elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const statusDiv = document.getElementById('status');
        const errorDiv = document.getElementById('error');
        const debugLog = document.getElementById('debugLog');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');

        // Debug logging function
        function logDebug(message) {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.textContent = `[${timestamp}] ${message}`;
            debugLog.appendChild(logEntry);
            debugLog.scrollTop = debugLog.scrollHeight;
        }

        // Show error message
        function showError(message) {
            console.error('Error:', message);
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            statusDiv.textContent = 'Error occurred';
            statusDiv.classList.remove('connecting', 'connected');
            statusDiv.classList.add('error');
            logDebug(`ERROR: ${message}`);
        }

        // Start camera function
        async function startCamera() {
            logDebug('Starting camera...');

            try {
                const constraints = {
                    video: {
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        },
                        facingMode: 'user'
                    },
                    audio: false
                };

                logDebug('Requesting camera access...');
                statusDiv.textContent = 'Requesting camera access...';
                statusDiv.classList.remove('connected', 'error');
                statusDiv.classList.add('connecting');

                stream = await navigator.mediaDevices.getUserMedia(constraints);
                logDebug('Camera access granted');

                video.srcObject = stream;

                await new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        // Set canvas dimensions to match video
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        video.play();
                        resolve();
                    };
                });

                statusDiv.textContent = 'Camera started';
                statusDiv.classList.remove('connecting', 'error');
                statusDiv.classList.add('connected');
                startBtn.disabled = true;
                stopBtn.disabled = false;
                errorDiv.classList.add('hidden');

                processing = true;
                frameInterval = setInterval(processFrame, 1000 / TARGET_FPS);
                logDebug('Camera started successfully');

            } catch (err) {
                console.error('Camera error:', err);
                let errorMessage = 'Error accessing camera: ';

                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Camera access was denied. Please allow camera access and try again.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'No camera found. Please connect a camera and try again.';
                } else if (err.name === 'NotReadableError') {
                    errorMessage +=
                        'Camera is in use by another application. Please close other applications using the camera and try again.';
                } else {
                    errorMessage += err.message;
                }

                showError(errorMessage);
                stopCamera();
            }
        }

        // Stop camera function
        function stopCamera() {
            logDebug('Stopping camera...');
            stopBtn.style.backgroundColor = '#4B5563';

            processing = false;
            clearInterval(frameInterval);

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            if (video.srcObject) {
                video.srcObject = null;
            }

            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            statusDiv.textContent = 'Camera stopped';
            statusDiv.classList.remove('connected');
            startBtn.disabled = false;
            stopBtn.disabled = true;

            stopBtn.style.backgroundColor = '';
            logDebug('Camera stopped');
        }

        // Process a single frame
        function processFrame() {
            if (!processing || !stream) return;

            try {
                // Ensure canvas size matches video feed before drawing
                if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                }
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                if (socket && socket.readyState === WebSocket.OPEN) {
                    canvas.toBlob((blob) => {
                        if (blob) {
                            const reader = new FileReader();
                            reader.onload = () => {
                                if (socket.readyState === WebSocket.OPEN) {
                                    socket.send(reader.result);
                                }
                            };
                            reader.readAsArrayBuffer(blob);
                        }
                    }, 'image/jpeg', 0.85);
                }
            } catch (err) {
                console.error('Frame processing error:', err);
                showError('Error processing video frame');
                stopCamera();
            }
        }

        // Draw bounding boxes on canvas
        function drawDetections(detections) {
            // Redraw video frame before drawing detections
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            if (!detections || detections.length === 0) {
                return;
            }

            // Calculate scaling factors
            // Assuming the server returns coordinates based on a 320x240 frame
            const serverFrameWidth = 320;
            const serverFrameHeight = 240;
            const scaleX = canvas.width / serverFrameWidth;
            const scaleY = canvas.height / serverFrameHeight;

            // Draw detections
            detections.forEach(det => {
                const [x1, y1, x2, y2] = det.bbox;
                const color = det.confidence > 0.7 ? '#00FF00' : '#FF0000'; // Green for verified, Red otherwise

                // Scale coordinates
                const scaledX1 = x1 * scaleX;
                const scaledY1 = y1 * scaleY;
                const scaledX2 = x2 * scaleX;
                const scaledY2 = y2 * scaleY;

                // Draw bounding box
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
                ctx.strokeRect(scaledX1, scaledY1, scaledX2 - scaledX1, scaledY2 - scaledY1);

                // Draw label background
                const text = `${det.label} (${det.confidence.toFixed(2)})`;
                const textWidth = ctx.measureText(text).width;
                ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
                // Position label above the box
                ctx.fillRect(
                    scaledX1 - 1,
                    scaledY1 - 25,
                    textWidth + 10,
                    20
                );

                // Draw label text
                ctx.fillStyle = color;
                ctx.font = '16px Arial';
                // Position text inside the label background
                ctx.fillText(text, scaledX1 + 5, scaledY1 - 10);

                // Only log verified faces (confidence > 0.7)
                if (det.confidence > 0.7) {
                    logDebug(`Verified: ${det.label} (confidence: ${det.confidence.toFixed(2)})`);
                }
            });
        }

        // Initialize WebSocket connection
        function connectWebSocket() {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            // Ensure this matches your Python server address
            const wsUrl = `${protocol}//localhost:5000/ws`;

            logDebug('Connecting to WebSocket...');
            socket = new WebSocket(wsUrl);

            socket.onopen = () => {
                statusDiv.textContent = 'Connected to server';
                statusDiv.classList.remove('connecting', 'error');
                statusDiv.classList.add('connected');
                logDebug('WebSocket connected');
            };

            socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                statusDiv.textContent = 'Connection error';
                statusDiv.classList.remove('connected', 'connecting');
                statusDiv.classList.add('error');
                logDebug('WebSocket error occurred');
                // Attempt to reconnect after a delay
                setTimeout(connectWebSocket, 5000);
            };

            socket.onclose = (event) => {
                let closeReason = 'Disconnected from server';
                if (event.reason) {
                    closeReason += `: ${event.reason}`;
                }
                statusDiv.textContent = closeReason;
                statusDiv.classList.remove('connected', 'connecting');
                statusDiv.classList.add('error');
                logDebug(`WebSocket disconnected: Code ${event.code}, Reason: ${event.reason}`);
                stopCamera();
                // Attempt to reconnect after a delay, unless it was a clean close (code 1000)
                if (event.code !== 1000) {
                    setTimeout(connectWebSocket, 5000);
                }
            };

            socket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);

                    if (data.status === 'success') {
                        // Update status with verification message
                        statusDiv.textContent = data.message;
                        statusDiv.classList.remove('connecting', 'error');
                        statusDiv.classList.add('connected');

                        // Log verification success
                        logDebug(`Verification successful: ${data.message}`);

                        // Store verification status in session
                        fetch('/face/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                verified: true,
                                label: data.label
                            })
                        }).then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error('Failed to update verification status on server.');
                        }).then(data => {
                            if (data.status === 'success') {
                                logDebug('Server update successful. Redirecting...');
                                // Redirect to dashboard after successful verification
                                window.location.href = '/dashboard';
                            } else {
                                throw new Error(data.message || 'Server verification failed');
                            }
                        }).catch(error => {
                            logDebug(`Error updating verification status: ${error.message}`);
                            showError('Failed to complete verification. Please try again.');
                            // Optionally clear face_authenticated session here if server update fails
                            // to prevent access to protected routes.
                            fetch('/face/clear-session', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                }
                            });
                        });
                    } else if (data.results) {
                        drawDetections(data.results);
                    } else if (data.error) {
                        showError(data.error);
                    } else {
                        logDebug(`Received unhandled message: ${JSON.stringify(data)}`);
                    }
                } catch (e) {
                    console.error('Error processing WebSocket message:', e);
                    logDebug(`Error processing WebSocket message: ${e.message}`);
                    showError('Error processing server data.');
                }
            };
        }

        // Add event listeners when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            logDebug('Initializing face authentication page...');
            // Ensure canvas aspect ratio is set correctly initially
            const videoContainer = document.getElementById('video-container');
            const aspectRatio = 640 / 480; // Match your expected video resolution ratio
            const containerWidth = videoContainer.offsetWidth;
            videoContainer.style.height = `${containerWidth / aspectRatio}px`;

            connectWebSocket();
            startCamera();
        });

        // Cleanup on page exit
        window.addEventListener('beforeunload', () => {
            logDebug('Cleaning up...');
            stopCamera();
            if (socket) {
                socket.close();
            }
            // Optionally clear face_authenticated session on page unload
            // to ensure user must re-verify if they leave the page before completion.
            fetch('/face/clear-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        });

        // Adjust canvas size if window is resized (optional, but good for responsiveness)
        window.addEventListener('resize', () => {
            const videoContainer = document.getElementById('video-container');
            const aspectRatio = 640 / 480; // Match your expected video resolution ratio
            const containerWidth = videoContainer.offsetWidth;
            videoContainer.style.height = `${containerWidth / aspectRatio}px`;
            // Re-set canvas dimensions based on container size
            canvas.width = videoContainer.offsetWidth;
            canvas.height = videoContainer.offsetHeight;
            logDebug(`Window resized. Canvas set to ${canvas.width}x${canvas.height}`);
        });
    </script>
</x-guest-layout>
