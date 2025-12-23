<?php
// 1. Logic Block (Process Form)
require_once 'config/db.php'; // Connect DB specifically for logic
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['student_name'];
    $father = $_POST['father_name'];
    $mother = $_POST['mother_name'];
    $phone = $_POST['phone'];
    $class = $_POST['class_req'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    // Insert into Database
    $sql = "INSERT INTO admission_applications (student_name, father_name, mother_name, phone, class_req, dob, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$name, $father, $mother, $phone, $class, $dob, $gender, $address])) {
        $message = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> আবেদন সফলভাবে জমা হয়েছে! আমরা শীঘ্রই যোগাযোগ করব।</div>";
    } else {
        $message = "<div class='alert alert-error'><i class='fas fa-exclamation-triangle'></i> দুঃখিত, কোথাও ভুল হয়েছে। আবার চেষ্টা করুন।</div>";
    }
}

// 2. Load the Template Header
require 'includes/header.php'; 
?>

<div class="container" style="margin-top: 30px; margin-bottom: 30px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: var(--primary);">অনলাইন ভর্তি ফরম</h1>
        <p style="color: #666;">সঠিক তথ্য দিয়ে ফরমটি পূরণ করুন</p>
    </div>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        
        <?php echo $message; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">শিক্ষার্থীর নাম</label>
                    <input type="text" name="student_name" class="form-control" placeholder="Student Name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">মোবাইল নম্বর</label>
                    <input type="text" name="phone" class="form-control" placeholder="017xxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label class="form-label">পিতার নাম</label>
                    <input type="text" name="father_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">মাতার নাম</label>
                    <input type="text" name="mother_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">কাঙ্খিত শ্রেণী</label>
                    <select name="class_req" class="form-control" required>
                        <option value="">নির্বাচন করুন</option>
                         <option value="Nursery">নার্সারী (Nursery)</option>
                        <option value="KG">কেজি (KG)</option>
                        <option value="1">প্রথম (Class 1)</option>
                        <option value="2">দ্বিতীয় (Class 2)</option>
                        <option value="3">তৃতীয় (Class 3)</option>
                        <option value="4">চতুর্থ (Class 4)</option>
                        <option value="5">পঞ্চম (Class 5)</option>
                        <option value="6">ষষ্ঠ (Class 6)</option>
                        <option value="7">সপ্তম (Class 7)</option>
                        <option value="8">অষ্টম (Class 8)</option>
                        <option value="9 Science">নবম (Science)</option>
                        <option value="9 Arts">নবম (Arts)</option>
                        <option value="9 Commerce">নবম (Commerce)</option>
                        <option value="11 Science">একাদশ (Science)</option>
                        <option value="11 Arts">একাদশ (Arts)</option>
                        <option value="11 Commerce">একাদশ (Commerce)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">জন্ম তারিখ</label>
                    <input type="date" name="dob" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">লিঙ্গ</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male">বালক (Male)</option>
                        <option value="Female">বালিকা (Female)</option>
                    </select>
                </div>
                <div class="form-group" >
                    <label class="form-label">স্থায়ী ঠিকানা</label>
                    <input type="text" name="address" class="form-control"  placeholder="স্থায়ী ঠিকানা লিখুন" required></input>
                </div>
            </div>
            
           

            <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem; font-weight: bold;">
                <i class="fas fa-paper-plane"></i> আবেদন জমা দিন
            </button>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>