<?php 
require 'config/db.php'; 
$config_res = $conn->query("SELECT setting_key, setting_value FROM settings");
$config = [];
while($r = $config_res->fetch_assoc()) { $config[$r['setting_key']] = $r['setting_value']; }
$master_pin = $config['global_AI_pin'] ?? '1234';
$can_capture = $config['capture_unknown'] ?? '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>AI Biometric AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        :root { --primary: #00f2ff; --bg: #05070a; --danger: #ff4b4b; --success: #00ffa3; }
        body { background: var(--bg); color: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
        
        /* Camera Area */
        .viewport { position: relative; height: 50vh; background: #000; border-bottom: 2px solid var(--primary); }
        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        canvas { position: absolute; top: 0; left: 0; z-index: 5; transform: scaleX(-1); }
        
        .scan-line { position: absolute; width: 100%; height: 3px; background: var(--primary); top: 0; box-shadow: 0 0 15px var(--primary); animation: scan 3s infinite linear; z-index: 10; }
        @keyframes scan { 0% { top: 0; } 100% { top: 100%; } }

        /* Results Area */
        .info-panel { flex-grow: 1; padding: 20px; overflow-y: auto; background: linear-gradient(to bottom, #0f141e, #05070a); }
        .user-card { 
            background: rgba(255,255,255,0.05); border: 1px solid rgba(0,242,255,0.2); 
            border-radius: 12px; padding: 12px; display: flex; align-items: center; margin-bottom: 10px;
            animation: slideIn 0.3s ease-out;
        }
        .status-ignored { border-color: var(--danger); }
        .status-success { border-color: var(--success); }
        .emp-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; margin-right: 15px; border: 1px solid var(--primary); }

        /* Toasts */
        .AI-toast {
            position: absolute; top: 20px; left: 50%; transform: translateX(-50%);
            z-index: 100; padding: 10px 20px; border-radius: 20px; font-weight: bold;
            backdrop-filter: blur(10px); animation: toastFade 2s forwards;
        }
        @keyframes toastFade { 0% { opacity:0; top:10px; } 20% { opacity:1; top:20px; } 80% { opacity:1; } 100% { opacity:0; } }

        #lock-screen { position: fixed; inset: 0; background: var(--bg); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .pin-input { background: transparent; border: none; border-bottom: 2px solid var(--primary); color: var(--primary); font-size: 3rem; text-align: center; width: 200px; outline: none; }
    </style>
</head>
<body>

    <div id="lock-screen">
        <h4 class="mb-4">SYSTEM LOCKED</h4>
        <input type="password" id="pin-field" class="pin-input" maxlength="4" placeholder="••••">
    </div>

    <div id="AI-body" style="display:none;">
        <div class="viewport" id="cam-container">
            <div class="scan-line"></div>
            <video id="video" autoplay muted playsinline></video>
        </div>

        <div class="info-panel">
            <h6 class="text-white-50 mb-3"><i class="fa fa-clock me-2"></i>LIVE ATTENDANCE FEED</h6>
            <div id="user-list"></div>
        </div>
    </div>

   <script>
    const MASTER_PIN = "<?= $master_pin ?>";
    const CAPTURE_UNKNOWN = <?= $can_capture ?>;
    let knownFaces = [];
    let empMeta = {};
    let scanCooldowns = new Map();
    let unknownLock = false;

    document.getElementById('pin-field').addEventListener('input', (e) => {
        if (e.target.value === MASTER_PIN) {
            document.getElementById('lock-screen').style.display = 'none';
            document.getElementById('AI-body').style.display = 'block';
            initAI();
        }
    });

    async function initAI() {
        try {
            await faceapi.nets.ssdMobilenetv1.loadFromUri('assets/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('assets/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('assets/models');

            const res = await fetch('api/fetch_faces.php');
            const users = await res.json();
            
            knownFaces = users.map(u => {
                empMeta[u.id] = u;
                const descriptors = JSON.parse(u.face_descriptors).map(d => new Float32Array(d));
                return new faceapi.LabeledFaceDescriptors(u.id.toString(), descriptors);
            });

            const faceMatcher = new faceapi.FaceMatcher(
                knownFaces.length > 0 ? knownFaces : [new faceapi.LabeledFaceDescriptors('void', [new Float32Array(128)])], 
                0.6
            );

            const video = document.getElementById('video');
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            video.srcObject = stream;

            video.onloadedmetadata = () => {
                const canvas = faceapi.createCanvasFromMedia(video);
                document.getElementById('cam-container').append(canvas);
                faceapi.matchDimensions(canvas, { width: video.clientWidth, height: video.clientHeight });

                setInterval(async () => {
                    const detections = await faceapi.detectAllFaces(video).withFaceLandmarks().withFaceDescriptors();
                    const resized = faceapi.resizeResults(detections, { width: video.clientWidth, height: video.clientHeight });
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    resized.forEach(d => {
                        const match = faceMatcher.findBestMatch(d.descriptor);
                        const box = d.detection.box;
                        
                        // HUD Box
                        ctx.strokeStyle = match.label === 'unknown' ? '#ff4b4b' : '#00f2ff';
                        ctx.lineWidth = 3;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);

                        if (match.label !== 'unknown' && match.label !== 'void') {
                            if (!scanCooldowns.has(match.label)) logAttendance(match.label);
                        } else if (match.label === 'unknown') {
                            handleUnknown(video, box, d.descriptor);
                        }
                    });
                }, 700);
            };
        } catch (err) {
            console.error("AI Initialization Error:", err);
            showToast("Camera or Model Error", "#ff4b4b");
        }
    }

    async function logAttendance(id) {
        scanCooldowns.set(id, true);
        const user = empMeta[id];

        try {
            const response = await fetch('api/log_attendance.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();

            const card = document.createElement('div');
            // Support both 'ignored' and success states
            const isIgnored = result.status === 'ignored';
            
            card.className = `user-card ${isIgnored ? 'status-ignored' : 'status-success'}`;
            card.innerHTML = `
                <img src="${user.photo_path || 'assets/default.png'}" class="emp-img">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <b>${user.name}</b>
                        <small class="${isIgnored ? 'text-danger' : 'text-success'}">
                            ${isIgnored ? 'IGNORED' : 'SUCCESS'}
                        </small>
                    </div>
                    <div class="small text-white-50">${user.dept_name || 'Staff'}</div>
                </div>
            `;
            document.getElementById('user-list').prepend(card);
            
            // Remove card after 5 seconds and clear cooldown
            setTimeout(() => { 
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 500);
                scanCooldowns.delete(id); 
            }, 5000);
        } catch (e) {
            console.error("Log failed", e);
            scanCooldowns.delete(id);
        }
    }

   async function handleUnknown(video, box, descriptor) {
    if (CAPTURE_UNKNOWN == 0 || unknownLock) return;
    unknownLock = true;

    // 1. Create a hidden capture canvas
    const captureCanvas = document.createElement('canvas');
    const ctx = captureCanvas.getContext('2d');

    // 2. Map coordinates from Display Size to Real Video Resolution
    // This is the most common reason for black images
    const scaleX = video.videoWidth / video.clientWidth;
    const scaleY = video.videoHeight / video.clientHeight;

    // Set output size
    captureCanvas.width = 400;
    captureCanvas.height = 400;

    // Calculate source coordinates on the actual raw video feed
    const sx = box.x * scaleX;
    const sy = box.y * scaleY;
    const sw = box.width * scaleX;
    const sh = box.height * scaleY;

    // Draw the crop from the raw video stream
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, 400, 400);

    // 3. Validation: Check if image is actually captured
    const base64Image = captureCanvas.toDataURL('image/jpeg', 0.8);
    
    // Safety check: If the image is extremely small or mostly black, stop
    if (base64Image.length < 1000) {
        unknownLock = false;
        return;
    }

    showToast("Unknown Detected - Logging...", "#ff4b4b");

    try {
        const response = await fetch('api/log_unknown.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                image: base64Image, 
                descriptor: Array.from(descriptor) 
            })
        });
        const res = await response.json();
        console.log("Unknown Log Status:", res.status);
    } catch (err) {
        console.error("Unknown Log Error:", err);
    }

    // 15s cooldown to prevent spamming logs
    setTimeout(() => unknownLock = false, 15000);
}

    function showToast(msg, color) {
        const t = document.createElement('div');
        t.className = "AI-toast"; // Ensure this matches your CSS class
        t.style.background = color;
        t.innerText = msg;
        document.getElementById('cam-container').append(t);
        setTimeout(() => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 500);
        }, 2000);
    }
</script>
</body>
</html>