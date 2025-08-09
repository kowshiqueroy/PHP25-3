<?php
require_once 'includes/session.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        #video-container {
            position: relative;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div id="video-container">
                    <video id="video" width="720" height="560" autoplay muted></video>
                </div>
                <button id="capture" class="btn btn-primary">Check-in / Check-out</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture');

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.faceLandmark68Net.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.faceRecognitionNet.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.ssdMobilenetv1.loadFromUri(BASE_URL + 'assets/js/face-api-models')
        ]).then(startVideo);

        function startVideo() {
            navigator.getUserMedia(
                { video: {} },
                stream => video.srcObject = stream,
                err => console.error(err)
            );
        }

        captureButton.addEventListener('click', async () => {
            const canvas = faceapi.createCanvasFromMedia(video);
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);
            const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
            if (detections) {
                const descriptor = detections.descriptor;
                // Send descriptor to server for matching
                fetch('process_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ descriptor: Array.from(descriptor) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Welcome, ${data.staff.name}!`);
                    } else {
                        alert(data.message);
                    }
                });
            }
        });
    </script>
</body>
</html>