<?php 
require 'includes/header.php'; 

// --- SERVER SIDE: SAVE OR MERGE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $conn->real_escape_string($_POST['emp_id']);
    $new_descriptors = json_decode($_POST['face_descriptors'], true);

    // Check if ID exists
    $check = $conn->query("SELECT id, face_descriptors FROM employees WHERE emp_id = '$emp_id'");
    
    if ($check->num_rows > 0) {
        // --- MODE: IMPROVE RECOGNITION ---
        $existing = $check->fetch_assoc();
        $old_descriptors = json_decode($existing['face_descriptors'], true) ?: [];
        $merged = array_merge($old_descriptors, $new_descriptors);
        
        $stmt = $conn->prepare("UPDATE employees SET face_descriptors = ? WHERE id = ?");
        $stmt->bind_param("si", json_encode($merged), $existing['id']);
        $stmt->execute();
        echo "<script>alert('Recognition improved for existing employee!'); window.location='dashboard.php';</script>";
    } else {
        // --- MODE: NEW ENROLLMENT ---
        $name = $conn->real_escape_string($_POST['name']);
        $dept = intval($_POST['department']);
        $pos = $conn->real_escape_string($_POST['position']);
        $join = $_POST['joining_date'];

        $target = "assets/photos/" . $emp_id . ".jpg";
        if(isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
            move_uploaded_file($_FILES['photo']['tmp_name'], $target);
        } else {
            $target = "assets/photos/default.jpg";
        }

        $stmt = $conn->prepare("INSERT INTO employees (name, emp_id, department_id, position, joining_date, face_descriptors, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $name, $emp_id, $dept, $pos, $join, $_POST['face_descriptors'], $target);
        $stmt->execute();
        echo "<script>alert('New employee enrolled successfully!'); window.location='dashboard.php';</script>";
    }
}
?>

<div class="container pt-3">
    <div class="glass-card">
        <h4 class="mb-4 text-white"><i class="fa fa-fingerprint text-info"></i> Smart Biometric Enrollment</h4>
        
        <div class="row g-4">
            <div class="col-md-6 text-center">
                <div class="position-relative rounded-4 overflow-hidden border border-secondary bg-black" style="height: 380px;">
                    <video id="video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-dark bg-opacity-75 border-top border-info border-opacity-25">
                        <h6 id="step-text" class="text-info m-0 fw-bold">Step 1: Look Directly at Camera</h6>
                    </div>

                    <div id="scan-loading" class="position-absolute top-50 start-50 translate-middle d-none">
                        <div class="spinner-border text-info" style="width: 3rem; height: 3rem;"></div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-center gap-2 align-items-center">
                    <button id="capBtn" class="btn btn-primary rounded-pill px-4 shadow">
                        <i class="fa fa-camera me-2"></i>Capture Angle
                    </button>
                    <div id="badge-list" class="fs-4"></div>
                </div>
            </div>

            <div class="col-md-6">
                <div id="id-alert" class="alert alert-warning d-none border-0 bg-warning bg-opacity-10 text-warning mb-3">
                    <i class="fa fa-sync fa-spin me-2"></i> <span id="alert-msg">Duplicate detected. Improvement mode active.</span>
                </div>

                <form method="POST" enctype="multipart/form-data" id="enrollForm">
                    <input type="hidden" name="face_descriptors" id="face_input">
                    
                    <div class="mb-3">
                        <label class="small text-white-50">Employee ID</label>
                        <input type="text" name="emp_id" id="emp_id_field" class="form-control bg-dark text-white border-secondary" placeholder="EMP101" required>
                    </div>

                    <div id="meta-fields">
                        <div class="mb-3">
                            <label class="small text-white-50">Full Name</label>
                            <input type="text" name="name" id="name_field" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="small text-white-50">Department</label>
                                <select name="department" id="department" class="form-select bg-dark text-white border-secondary">
                                    <?php 
                                    $d = $conn->query("SELECT * FROM departments");
                                    while($r=$d->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']}</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="small text-white-50">Joining Date</label>
                                <input type="date" name="joining_date" id="joining_date" class="form-control bg-dark text-white border-secondary" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small text-white-50">Job Position</label>
                            <input type="text" name="position" id="position" class="form-control bg-dark text-white border-secondary">
                        </div>

                        <div class="mb-3">
                            <label class="small text-white-50">Profile Photo</label>
                            <input type="file" name="photo" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" id="saveBtn" class="btn btn-success w-100 py-3 fw-bold shadow mt-2" disabled>
                        ENROLL NEW EMPLOYEE
                    </button>
                    
                    <button type="button" id="resetBtn" class="btn btn-link text-white-50 w-100 mt-2 d-none" onclick="location.reload()">Cancel / Reset Form</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let scans = [];
    const vid = document.getElementById('video');
    const stepText = document.getElementById('step-text');
    const idField = document.getElementById('emp_id_field');
    const saveBtn = document.getElementById('saveBtn');
    
    const steps = [
        "Step 1: Look STRAIGHT at Camera",
        "Step 2: Turn head slightly LEFT",
        "Step 3: Turn head slightly RIGHT",
        "Capture Complete!"
    ];

    // --- AI INITIALIZATION ---
    async function init() {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri('assets/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('assets/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('assets/models')
        ]);
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        vid.srcObject = stream;
    }
    init();

    // --- DUPLICATE FACE CHECK ---
    async function checkFaceDuplicate(descriptor) {
        const response = await fetch('api/fetch_faces.php');
        const employees = await response.json();
        
        for (let emp of employees) {
            const stored = JSON.parse(emp.face_descriptors).map(d => new Float32Array(d));
            for (let storedDesc of stored) {
                const distance = faceapi.euclideanDistance(descriptor, storedDesc);
                if (distance < 0.5) return emp; // Threshold 0.5 = Same Person
            }
        }
        return null;
    }

    // --- UI TRANSFORMATION LOGIC ---
    function setImprovementMode(match) {
        idField.value = match.emp_id;
        document.getElementById('name_field').value = match.name;
        document.getElementById('name_field').readOnly = true;
        
        // Hide metadata that won't be changed
        document.getElementById('department').style.display = "none";
        document.getElementById('joining_date').style.display = "none";
        document.getElementById('position').style.display = "none";
        
        document.getElementById('meta-fields').style.opacity = "0.5";
        document.getElementById('id-alert').classList.remove('d-none');
        document.getElementById('alert-msg').innerHTML = `Face matched with <b>${match.name}</b>. Improvement Mode Active.`;
        document.getElementById('resetBtn').classList.remove('d-none');
        
        saveBtn.innerText = "IMPROVE RECOGNITION DATA";
        saveBtn.classList.replace('btn-success', 'btn-warning');
    }

    // --- CAPTURE BUTTON CLICK ---
    document.getElementById('capBtn').addEventListener('click', async () => {
        document.getElementById('scan-loading').classList.remove('d-none');
        const det = await faceapi.detectSingleFace(vid).withFaceLandmarks().withFaceDescriptor();
        document.getElementById('scan-loading').classList.add('d-none');

        if (det) {
            // Check for duplicate face on the very first scan
            if (scans.length === 0) {
                const match = await checkFaceDuplicate(det.descriptor);
                if (match) {
                    setImprovementMode(match);
                }
            }

            scans.push(Array.from(det.descriptor));
            document.getElementById('badge-list').innerHTML += '<i class="fa fa-check-circle text-success ms-1"></i>';
            
            if (scans.length < 3) {
                stepText.innerText = steps[scans.length];
            } else {
                stepText.innerText = steps[3];
                stepText.className = "text-success fw-bold";
                document.getElementById('face_input').value = JSON.stringify(scans);
                document.getElementById('capBtn').disabled = true;
                saveBtn.disabled = false;
            }
        } else {
            alert("Face not detected. Ensure good lighting and stay still.");
        }
    });

    // --- MANUAL ID INPUT CHECK ---
    idField.addEventListener('input', async function() {
        const res = await fetch(`api/check_id.php?emp_id=${this.value}`);
        const data = await res.json();
        if (data.exists) {
            saveBtn.innerText = "IMPROVE RECOGNITION DATA";
            saveBtn.classList.replace('btn-success', 'btn-warning');
        } else {
            saveBtn.innerText = "ENROLL NEW EMPLOYEE";
            saveBtn.classList.replace('btn-warning', 'btn-success');
        }
    });
</script>

<?php require 'includes/footer.php'; ?>