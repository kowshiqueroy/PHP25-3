<!DOCTYPE html>
<html>
<head>
    <title>Face Recognition Test</title>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body>
    <video id="video" width="720" height="560" autoplay muted></video>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const video = document.getElementById('video');

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.faceLandmark68Net.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.faceRecognitionNet.loadFromUri(BASE_URL + 'assets/js/face-api-models'),
            faceapi.nets.faceExpressionNet.loadFromUri(BASE_URL + 'assets/js/face-api-models')
        ]).then(startVideo);

        function startVideo() {
            navigator.getUserMedia(
                { video: {} },
                stream => video.srcObject = stream,
                err => console.error(err)
            );
        }

        video.addEventListener('play', () => {
            const canvas = faceapi.createCanvasFromMedia(video);
            document.body.append(canvas);
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);
            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceExpressions();
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawDetections(canvas, resizedDetections);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                faceapi.draw.drawFaceExpressions(canvas, resizedDetections);
            }, 100);
        });
    </script>
</body>
</html>