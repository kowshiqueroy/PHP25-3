<?php 
require 'includes/header.php'; 
$id = intval($_GET['id']);
$emp = $conn->query("SELECT * FROM employees WHERE id=$id")->fetch_assoc();

// 1. UPDATE TEXT DETAILS
if(isset($_POST['update_details'])) {
    $name = $_POST['name'];
    $dept = $_POST['department'];
    $pos = $_POST['position'];
    
    // Photo Update
    if(isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        move_uploaded_file($_FILES['photo']['tmp_name'], $emp['photo_path']);
    }

    $stmt = $conn->prepare("UPDATE employees SET name=?, department_id=?, position=? WHERE id=?");
    $stmt->bind_param("sisi", $name, $dept, $pos, $id);
    $stmt->execute();
    echo "<script>window.location='edit_employee.php?id=$id';</script>";
}

// 2. ADD NEW FACE SAMPLE (APPEND)
if(isset($_POST['new_face_data'])) {
    $new_desc = json_decode($_POST['new_face_data']); // The single new array
    
    // Get existing
    $current_json = $emp['face_descriptors'];
    $current_data = json_decode($current_json, true);
    
    // Append
    $current_data[] = $new_desc;
    
    // Save back
    $final_json = json_encode($current_data);
    
    $stmt = $conn->prepare("UPDATE employees SET face_descriptors=? WHERE id=?");
    $stmt->bind_param("si", $final_json, $id);
    $stmt->execute();
    echo "<script>alert('Face data added! Recognition improved.'); window.location='edit_employee.php?id=$id';</script>";
}

// 3. RESET FACE DATA (Clear all)
if(isset($_POST['reset_faces'])) {
    $empty = json_encode([]);
    $conn->query("UPDATE employees SET face_descriptors='$empty' WHERE id=$id");
    echo "<script>window.location='edit_employee.php?id=$id';</script>";
}
?>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="glass-card">
            <h4 class="mb-3">Edit Profile</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="text-center mb-3">
                    <img src="<?= $emp['photo_path'] ?>?t=<?=time()?>" class="rounded-circle border border-info" width="100">
                    <input type="file" name="photo" class="form-control form-control-sm mt-2">
                </div>
                
                <label>Name</label>
                <input type="text" name="name" class="form-control mb-2" value="<?= $emp['name'] ?>">
                
                <label>Department</label>
                <select name="department" class="form-select mb-2">
                    <?php 
                    $d = $conn->query("SELECT * FROM departments"); 
                    while($r=$d->fetch_assoc()): 
                    ?>
                    <option value="<?= $r['id'] ?>" <?= $emp['department_id']==$r['id']?'selected':'' ?>><?= $r['name'] ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Position</label>
                <input type="text" name="position" class="form-control mb-3" value="<?= $emp['position'] ?>">
                
                <button name="update_details" class="btn btn-primary w-100">Update Details</button>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="m-0">Improve AI</h4>
                <span class="badge bg-info text-dark">
                    <?= count(json_decode($emp['face_descriptors'])) ?> Samples Stored
                </span>
            </div>
            
            <div style="position: relative; border-radius: 10px; overflow: hidden; border: 2px solid #555; background: #000;">
                <video id="video" autoplay muted style="width: 100%; height: 250px; object-fit: cover;"></video>
                <canvas id="overlay" style="position: absolute; top:0; left:0;"></canvas>
            </div>

            <div class="mt-3">
                <button id="scanBtn" class="btn btn-success w-100 mb-2">
                    <i class="fa fa-camera"></i> Scan & Add Face
                </button>
                
                <form method="POST" style="display:inline;">
                    <button name="reset_faces" class="btn btn-outline-danger w-100 btn-sm" onclick="return confirm('Delete ALL face data for this user?')">
                        Reset Face Data
                    </button>
                </form>
            </div>

            <form method="POST" id="faceForm">
                <input type="hidden" name="new_face_data" id="new_face_input">
            </form>
        </div>
    </div>
</div>

<script>
    const vid = document.getElementById('video');
    let isModelLoaded = false;

    // Load Models
    Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri('assets/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('assets/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('assets/models')
    ]).then(() => {
        isModelLoaded = true;
        navigator.mediaDevices.getUserMedia({video:{}}).then(s => vid.srcObject = s);
    });

    document.getElementById('scanBtn').addEventListener('click', async () => {
        if(!isModelLoaded) return alert("AI Loading...");
        
        const detection = await faceapi.detectSingleFace(vid).withFaceLandmarks().withFaceDescriptor();
        
        if(detection) {
            // Confirm with user
            if(confirm("Face detected! Add this sample to database?")) {
                const descriptorArray = Array.from(detection.descriptor);
                document.getElementById('new_face_input').value = JSON.stringify(descriptorArray);
                document.getElementById('faceForm').submit();
            }
        } else {
            alert("No face seen. Look at the camera.");
        }
    });
</script>

<div style="height: 80px;"></div>
</body></html>