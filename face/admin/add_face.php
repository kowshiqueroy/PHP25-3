<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header("Location: ../login.php");
    exit();
}

$staff = get_all_staff();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_face_descriptor'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $staff_id = $_POST['staff_id'];
    $descriptor = $_POST['descriptor'];

    if (!empty($staff_id) && !empty($descriptor)) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO face_images (staff_id, image_path, descriptor) VALUES (?, ?, ?)");
        $image_path = 'uploads/' . $staff_id . '-' . uniqid() . '.jpg'; // Placeholder path
        $stmt->bind_param("iss", $staff_id, $image_path, $descriptor);
        if ($stmt->execute()) {
            $message = "Face descriptor added successfully!";
        } else {
            $error = "Failed to save face descriptor. Does this staff member already have a face registered?";
        }
    } else {
        $error = "Staff and a valid face descriptor are required.";
    }
}

?>
<?php require_once 'header.php'; ?>

<style>
    #video-container {
        position: relative;
        width: 500px;
        height: 380px;
        border: 2px solid #ddd;
        background: #333;
    }
    #video, #snapshot-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    #snapshot-canvas {
        z-index: 10;
    }
    .status-badge {
        font-size: 1rem;
        padding: 0.5em 0.8em;
    }
</style>

<div class="container mt-4">
    <h2>Add Face by Camera Capture</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>1. Start Camera & Take Snapshot</span>
                    <span id="model-status" class="badge badge-warning status-badge">Loading AI Models...</span>
                </div>
                <div class="card-body text-center">
                    <div id="video-container" class="mx-auto mb-3">
                        <video id="video" autoplay muted playsinline></video>
                        <canvas id="snapshot-canvas"></canvas>
                    </div>
                    <button id="start-camera" class="btn btn-secondary" disabled>Start Camera</button>
                    <button id="take-snapshot" class="btn btn-primary" disabled>Take Snapshot</button>
                    <div id="snapshot-result" class="mt-2 font-weight-bold"></div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">2. Select Staff & Add Face</div>
                <div class="card-body">
                    <form id="add-face-form" action="add_face.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="descriptor" id="descriptor">
                        <input type="hidden" name="add_face_descriptor" value="1">
                        <div class="form-group">
                            <label for="staff_id">Staff Member</label>
                            <select name="staff_id" id="staff_id" class="form-control" required>
                                <option value="">-- Select Staff --</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" id="add-face-btn" class="btn btn-success btn-block" disabled>Add Face to Selected Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const video = document.getElementById('video');
    const snapshotCanvas = document.getElementById('snapshot-canvas');
    const modelStatus = document.getElementById('model-status');
    const snapshotResult = document.getElementById('snapshot-result');
    const startCameraButton = document.getElementById('start-camera');
    const takeSnapshotButton = document.getElementById('take-snapshot');
    const addFaceButton = document.getElementById('add-face-btn');
    const staffSelect = document.getElementById('staff_id');
    const descriptorInput = document.getElementById('descriptor');
    const addFaceForm = document.getElementById('add-face-form');

    let stream = null;
    let faceDescriptor = null;

    async function loadModels() {
        const MODEL_URL = BASE_URL + 'assets/js/face-api-models';
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelStatus.textContent = 'Models Loaded';
            modelStatus.classList.remove('badge-warning');
            modelStatus.classList.add('badge-success');
            startCameraButton.disabled = false;
        } catch (e) {
            modelStatus.textContent = 'Model Load Error';
            modelStatus.classList.remove('badge-warning');
            modelStatus.classList.add('badge-danger');
            console.error("Error loading models: ", e);
        }
    }
    loadModels();

    startCameraButton.addEventListener('click', async () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        video.style.display = 'block';
        snapshotCanvas.style.display = 'none';
        snapshotResult.textContent = '';
        addFaceButton.disabled = true;
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            video.srcObject = stream;
            takeSnapshotButton.disabled = false;
            startCameraButton.textContent = 'Restart Camera';
        } catch (err) {
            console.error("Error accessing camera: ", err);
            snapshotResult.textContent = 'Error: Could not access camera.';
            snapshotResult.style.color = 'red';
        }
    });

    takeSnapshotButton.addEventListener('click', async () => {
        if (!stream) return;

        snapshotResult.textContent = 'Processing...';
        snapshotResult.style.color = 'black';
        addFaceButton.disabled = true;
        faceDescriptor = null;

        const context = snapshotCanvas.getContext('2d');
        snapshotCanvas.width = video.videoWidth;
        snapshotCanvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, snapshotCanvas.width, snapshotCanvas.height);

        video.style.display = 'none';
        snapshotCanvas.style.display = 'block';

        try {
            const detection = await faceapi.detectSingleFace(snapshotCanvas).withFaceLandmarks().withFaceDescriptor();

            if (detection) {
                faceDescriptor = detection.descriptor;
                descriptorInput.value = JSON.stringify(Array.from(faceDescriptor));
                snapshotResult.textContent = 'Face Detected Successfully!';
                snapshotResult.style.color = 'green';
                addFaceButton.disabled = false;

                const dims = faceapi.matchDimensions(snapshotCanvas, { width: snapshotCanvas.width, height: snapshotCanvas.height });
                const resizedDetection = faceapi.resizeResults(detection, dims);
                faceapi.draw.drawDetections(snapshotCanvas, resizedDetection);
            } else {
                snapshotResult.textContent = 'No face detected. Please try again.';
                snapshotResult.style.color = 'red';
            }
        } catch (e) {
            console.error("Error during face detection: ", e);
            snapshotResult.textContent = 'An error occurred during face detection.';
            snapshotResult.style.color = 'red';
        }
    });

    addFaceForm.addEventListener('submit', (e) => {
        if (!staffSelect.value || !faceDescriptor) {
            e.preventDefault();
            alert('Please make sure you have selected a staff member and successfully captured a face.');
        }
    });

</script>

<?php require_once 'footer.php'; ?>
