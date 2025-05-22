<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <h2 class="text-2xl font-bold text-center mb-6">Face Authentication</h2>

            <div class="relative">
                <div id="video-container" class="relative">
                    <video id="video" class="w-full rounded-lg" autoplay playsinline></video>
                    <canvas id="canvas" class="absolute top-0 left-0 w-full h-full"></canvas>
                </div>

                <div id="status" class="mt-4 text-center text-gray-600">
                    Click Start Camera to begin
                </div>

                <div class="mt-4 flex justify-center space-x-4">
                    <button type="button" id="startBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Start Camera
                    </button>
                    <button type="button" id="stopBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" disabled>
                        Stop Camera
                    </button>
                </div>
            </div>

            <div id="error" class="mt-4 text-center text-red-600 hidden"></div>
            <div id="results" class="mt-4 text-center text-gray-600"></div>
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
        const resultsDiv = document.getElementById('results');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');

        // Show error message
        function showError(message) {
            console.error('Error:', message);
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            statusDiv.textContent = 'Error occurred';
            statusDiv.classList.add('text-red-600');
        }

        // Start camera function
        async function startCamera() {
            console.log('Start camera function called');
            startBtn.style.backgroundColor = '#4B5563'; // Visual feedback
            
            try {
                // Request camera access
                const constraints = {
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user'
                    },
                    audio: false
                };

                console.log('Requesting camera access...');
                statusDiv.textContent = 'Requesting camera access...';
                
                // Get camera stream
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                console.log('Camera access granted');
                
                // Set video source
                video.srcObject = stream;
                
                // Wait for video to be ready
                await new Promise((resolve) => {
                    video.onloadedmetadata = () => {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        video.play();
                        resolve();
                    };
                });

                // Update UI
                statusDiv.textContent = 'Camera started';
                statusDiv.classList.remove('text-red-600');
                statusDiv.classList.add('text-green-600');
                startBtn.disabled = true;
                stopBtn.disabled = false;
                errorDiv.classList.add('hidden');

                // Start processing frames
                processing = true;
                frameInterval = setInterval(processFrame, 1000 / TARGET_FPS);

            } catch (err) {
                console.error('Camera error:', err);
                let errorMessage = 'Error accessing camera: ';
                
                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Camera access was denied. Please allow camera access and try again.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'No camera found. Please connect a camera and try again.';
                } else if (err.name === 'NotReadableError') {
                    errorMessage += 'Camera is in use by another application. Please close other applications using the camera and try again.';
                } else {
                    errorMessage += err.message;
                }
                
                showError(errorMessage);
                stopCamera();
            } finally {
                startBtn.style.backgroundColor = ''; // Reset button color
            }
        }

        // Stop camera function
        function stopCamera() {
            console.log('Stop camera function called');
            stopBtn.style.backgroundColor = '#4B5563'; // Visual feedback
            
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
            
            // Update UI
            statusDiv.textContent = 'Camera stopped';
            statusDiv.classList.remove('text-green-600');
            startBtn.disabled = false;
            stopBtn.disabled = true;
            
            stopBtn.style.backgroundColor = ''; // Reset button color
        }

        // Process a single frame
        function processFrame() {
            if (!processing || !stream) return;

            try {
                // Draw video frame to canvas
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Send frame to server if WebSocket is connected
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

        // Update results display
        function updateResults(detections) {
            if (!detections || detections.length === 0) {
                return;
            }
            
            // Limit results display to 5 most recent detections
            if (resultsDiv.children.length > 5) {
                resultsDiv.removeChild(resultsDiv.firstChild);
            }
            
            detections.forEach(det => {
                const detectionDiv = document.createElement('div');
                detectionDiv.className = 'detection';
                
                const confidenceColor = det.confidence > 0.7 ? 'green' : 'red';
                detectionDiv.innerHTML = `
                    <strong>${det.label}</strong>
                    <span style="color: ${confidenceColor}">
                        (confidence: ${det.confidence.toFixed(2)})
                    </span>
                    <br>Model: ${det.model_type}
                    <br>Position: [${det.bbox.map(x => x.toFixed(1)).join(', ')}]
                `;
                
                resultsDiv.appendChild(detectionDiv);
            });
        }

        // Draw bounding boxes on canvas
        function drawDetections(detections) {
            if (!detections || detections.length === 0) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                return;
            }
            
            // Redraw video frame
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Draw detections
            detections.forEach(det => {
                const [x1, y1, x2, y2] = det.bbox;
                const color = det.confidence > 0.7 ? '#00FF00' : '#FF0000';
                
                // Draw bounding box
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
                ctx.strokeRect(x1, y1, x2 - x1, y2 - y1);
                
                // Draw label background
                const text = `${det.label} (${det.confidence.toFixed(2)})`;
                const textWidth = ctx.measureText(text).width;
                ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
                ctx.fillRect(
                    x1 - 1,
                    y1 - 25,
                    textWidth + 10,
                    20
                );
                
                // Draw label text
                ctx.fillStyle = color;
                ctx.font = '16px Arial';
                ctx.fillText(text, x1 + 5, y1 - 10);
            });
        }

        // Initialize WebSocket connection
        function connectWebSocket() {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${protocol}//localhost:5000/ws`;
            
            socket = new WebSocket(wsUrl);
            
            socket.onopen = () => {
                statusDiv.textContent = 'Connected to server';
                statusDiv.classList.remove('text-red-600');
                statusDiv.classList.add('text-green-600');
            };
            
            socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                statusDiv.textContent = 'Connection error';
                statusDiv.classList.remove('text-green-600');
                statusDiv.classList.add('text-red-600');
            };
            
            socket.onclose = () => {
                statusDiv.textContent = 'Disconnected from server';
                statusDiv.classList.remove('text-green-600');
                statusDiv.classList.add('text-red-600');
                stopCamera();
                setTimeout(connectWebSocket, 3000);
            };

            socket.onmessage = (event) => {
                try {
                    const detections = JSON.parse(event.data);
                    updateResults(detections);
                    drawDetections(detections);
                } catch (e) {
                    console.error('Error parsing detection data:', e);
                }
            };
        }

        // Add event listeners when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Adding event listeners...');
            startBtn.addEventListener('click', startCamera);
            stopBtn.addEventListener('click', stopCamera);
            console.log('Event listeners added');

            // Initialize WebSocket connection
            connectWebSocket();
        });

        // Cleanup on page exit
        window.addEventListener('beforeunload', () => {
            stopCamera();
            if (socket) {
                socket.close();
            }
        });
    </script>
</x-guest-layout>
