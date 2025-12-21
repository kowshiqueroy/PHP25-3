<?php
require_once '../../config.php'; // Provides $conn

$students = [];
$error_message = '';

// --- School Configuration (Update as needed) ---
$school_name_line_1 = "Parvej Khan";
$school_name_line_2 = "Residential School & College";
$school_address = "Harowa, Debirdanga, Nilphamari"; // Updated address
$school_phone = "01724162121"; // Example phone
$school_logo_path = "../../logo.png"; // Your provided logo path
$session = "2025-2026"; // Example session
// --------------------------------------------------

if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $search_id = $_GET['search_id'];
    $ids = [];

    if (strpos($search_id, ',') !== false) {
        $ids = explode(',', $search_id);
    } elseif (strpos($search_id, '-') !== false) {
        list($start, $end) = explode('-', $search_id);
        if (is_numeric($start) && is_numeric($end)) {
            $ids = range($start, $end);
        }
    } else {
        $ids[] = $search_id;
    }

    $ids = array_map('trim', $ids);
    $ids = array_filter($ids, 'is_numeric');
    
    if (!empty($ids)) {
        $ids_list = implode(',', $ids);
        
        if (isset($conn) && $conn instanceof mysqli) {
            $sql = "SELECT * FROM student WHERE id IN ($ids_list) ORDER BY id ASC";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $students[] = $row;
                }
            } else if (!$result) {
                $error_message = "Database query failed: " . mysqli_error($conn);
            } else {
                $error_message = "No students found with the provided Registration IDs: $ids_list";
            }
        } else {
             $error_message = "Database connection error. Check config.php.";
        }
    } else {
        $error_message = "No valid numeric Registration IDs were provided.";
    }
} else {
     $error_message = "Please provide a Registration ID (or a range/list).";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Student ID Cards</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS Reset */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0; /* Gray background for screen */
            font-family: 'Roboto', Arial, sans-serif;
            color: #333;
        }

        /* A4 Page Styles */
        @page {
            size: A4;
            margin: 10mm; /* Printer's margin */
        }

        .page {
            width: 190mm;  /* 210mm - 10mm - 10mm */
            height: 277mm; /* 297mm - 10mm - 10mm */
            margin: 0 auto;
            margin-bottom: 10mm;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            page-break-after: always;
            
            display: flex;
            flex-direction: column;
            justify-content: space-around; /* Evenly space the 3 card rows */
        }

        /* Row holds one student (front + back) */
        .card-row {
            display: flex;
            justify-content: center; /* Center the wrapper horizontally */
            width: 100%;
            page-break-inside: avoid;
        }

        /* Wrapper for one student's front and back */
        .student-card-wrapper {
            display: flex;
            gap: 1mm; /* Gutter between front and back */
            page-break-inside: avoid;
        }

        /* --- ID Card Base Style --- */
        .id-card {
            width: 52mm;
            height: 85mm;
            box-sizing: border-box;
            overflow: hidden;
            position: relative;
            background-color: #ffffff;
            border-radius: 3mm;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Shadow for screen */
            font-size: 7pt;
        }

        /* --- Card Front (Inspired by Image) --- */
        .card-front {
            background: 
                linear-gradient(to bottom, #d6f0f5 0%, #d6f0f5 25mm, #ffd700 25mm, #ffd700 30mm, #ffffff 30mm, #ffffff 100%); /* Teal, Yellow, White sections */
        }
        
        /* --- Card Front --- */
        .card-front .school-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 25mm; /* Height of the blue section */
            display: flex;
            align-items: flex-start; /* Align top */
            padding: 5mm;
            box-sizing: border-box;
            z-index: 2; /* Ensure it's above background */
        }
        
        .card-front .school-logo {
            width: 12mm;
            height: 12mm;
            object-fit: contain;
            margin-right: 3mm;
            background-color: #ffffffff; /* Lighter blue for logo background */
            border-radius: 2mm; /* Slightly rounded corners for logo container */
            padding: 1mm;
        }
        
        .card-front .school-info {
            color: #2e5c6a; /* Dark teal text */
            line-height: 1.1;
        }
        .card-front .school-name-main {
            font-size: 10pt;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
        }
        .card-front .school-name-sub {
            font-size: 7.5pt;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
        }
        .card-front .school-address-text {
            font-size: 4.8pt;
            font-weight: 500;
            margin: 1mm 0 0 0;
        }
        
        .card-front .id-card-side-text {
            position: absolute;
            right: 3mm; /* Closer to the edge */
            top: 80%;
            transform: translateY(-50%) rotate(90deg);
            transform-origin: 100% 50%;
            font-size: 10pt;
            font-weight: 900; /* Bold */
            color: #b0b0b0; /* Light gray */
            letter-spacing: 0.5mm;
            white-space: nowrap;
            z-index: 4;
        }

        .card-front .photo-container {
            position: relative;
            z-index: 5; /* Ensure photo is on top */
            text-align: center;
            margin-top: 18mm; /* Position of the photo relative to the top of the card */
        }
        .card-front .photo {
            width: 28mm;
            height: 28mm;
            background-color: #f0f0f0;
            object-fit: cover;
            border: 2px solid #ffffff; /* White border as in image */
            /* Hexagonal shape with clip-path */
            clip-path: polygon(50% 5%, 90% 25%, 90% 75%, 50% 95%, 10% 75%, 10% 25%);
            display: inline-block; /* To allow auto margins */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2); /* Shadow for depth */
        }
        
        .card-front .details-block {
            padding: 0 4mm;
            margin-top: 0mm;
            line-height: 1.5;
            position: relative;
            z-index: 30; /* Above background, below photo */
        }
        .card-front .detail-row {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 0.5mm;
        }
        .card-front .detail-label {
            font-weight: 500;
            width: 10mm; /* Adjusted width for labels */
            color: #555;
            flex-shrink: 0;
        }
        .card-front .detail-value {
            font-weight: 700;
            color: #000;
            flex-grow: 1;
            text-align: left;
        }

        .card-front .footer-contact {
            position: absolute;
            bottom: 4mm;
            left: 5mm;
            right: 5mm;
            display: flex;
            align-items: flex-end; /* Align items to the bottom */
            justify-content: space-between;
            z-index: 5;
        }
        .card-front .phone-number {
            font-size: 7.5pt;
            font-weight: 700;
            color: #2e5c6a; /* Dark teal */
            display: flex;
            align-items: center;
            line-height: 1; /* Tighter line height */
        }
        .card-front .phone-number i {
            margin-right: 2mm;
            color: #ffd700; /* Yellow accent */
            font-size: 8pt;
        }
        .card-front .principal-signature {
            text-align: right;
            line-height: 1.1;
        }
        .card-front .principal-signature img {
            height: 9mm; /* Adjusted height for signature image */
            width: auto;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-bottom: -3mm; /* Overlap text slightly */
        }
        .card-front .principal-signature p {
            font-size: 7pt;
            font-weight: 700;
            margin: 0;
            color: #555;
            font-family: 'Roboto', Arial, sans-serif; /* Use Roboto for print consistency */
        }
        
        /* --- Card Back --- */
        .id-card-back {
            padding: 4mm;
            box-sizing: border-box;
            background: #fdfdfd;
            display: flex;
            flex-direction: column;
        }
        
        .id-card-back .back-header {
            text-align: center;
            margin-bottom: 1mm;
            background-color: #d6f0f5; /* Light blue */
            padding: 2mm;
            border-radius: 2mm;
            color: #2e5c6a;
        }
        
        .id-card-back .back-header p {
            font-size: 6pt;
            font-weight: 500;
            margin: 0;
            line-height: 1;
        }
        
        .info-block {
            font-size: 5.5pt;
            text-align: center;
        }
        

        .emergency-contact {
            margin-top: 1mm;
            padding-top: 1mm;
            border-top: 1px dashed #ccc;
            text-align: center;
        }
        .emergency-contact h5 {
            font-size: 7.5pt;
            font-weight: 700;
            color: #8C1515; /* Maroon */
            margin: 0 0 1mm 0;
            text-align: center;
        }
        .emergency-contact p {
            font-size: 7pt;
            margin: 0;
            font-weight: 500;
            line-height: 1.4;
        }
        
        .qr-signature-block {
            
            margin-top: auto; /* Pushes this block to the bottom */
            padding-top: 4mm;
            border-top: 1px dashed #ccc;
            text-align: center;
        }
        
        .qr-code img {
            width: 20mm;
            height: 20mm;
        }

       

        /* --- Print Specific Styles --- */
        @media print {
            body, html {
                background-color: #fff;
            }
            .page {
                margin: 0;
                box-shadow: none;
                border: none;
            }
            .id-card {
                box-shadow: none; /* No shadow for print */
                /* For cutting guide, if desired */
                /* border: 0.2mm solid #ccc; */
            }
            .print-button, .print-button1, .error {
                display: none;
            }
        }

        /* On-screen controls */
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: 600;
                background-color: #2e5c6a;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                z-index: 100;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }
            .print-button1 {
               position: fixed;
                top: 20px;
                left: 20px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: 600;
                background-color: #2e5c6a;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                z-index: 100;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            }
        
        .error {
            text-align: center;
            font-size: 18px;
            color: #D8000C;
            background-color: #FFD2D2;
            padding: 20px;
            margin: 20px;
            border: 1px solid #D8000C;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<button class="print-button1" onclick="">Parvej Khan Residential School & College Website</button>

    <button class="print-button" onclick="window.print()">Print ID Cards</button>
   

    <?php if (!empty($students)) : ?>
        <?php
        $counter = 0;
        $total_students = count($students);
        $students_per_page = 3; // 3 rows * 1 student per row
        ?>

        <div class="page"> <?php foreach ($students as $student) : ?>
            
            <?php
            // Check for new page (every 3 students)
            if ($counter > 0 && $counter % $students_per_page == 0) {
                echo '</div>'; // End current page
                echo '<div class="page">'; // Start new page
            }
            ?>

            <div class="card-row">
                <div class="student-card-wrapper">
                
                    <div class="id-card card-front">
                        <div class="school-header">
                            <img src="<?php echo $school_logo_path; ?>" alt="Logo" class="school-logo" onerror="this.src='https://placehold.co/100x100/A2CCDD/2E5C6A?text=LOGO'">
                            <div class="school-info">
                                <div class="school-name-main"><?php echo htmlspecialchars($school_name_line_1); ?></div>
                                <div class="school-name-sub"><?php echo htmlspecialchars($school_name_line_2); ?></div>
                                <p class="school-address-text"><?php echo htmlspecialchars($school_address); ?></p>
                            </div>
                        </div>
                        
                        <div class="id-card-side-text">STUDENT ID CARD</div>

                        <div class="photo-container">
                            <img src="photo/<?php echo htmlspecialchars($student['photo']); ?>" alt="Photo" class="photo" 
                                 onerror="this.src='https://placehold.co/150x150/EFEFEF/AAAAAA?text=No+Photo'">
                        </div>
                        
                        <div class="details-block">
                            <div class="detail-row"><span class="detail-label">Name</span>:<span class="detail-value"><?php echo htmlspecialchars($student['name']); ?></span></div>
                            <div class="detail-row"><span class="detail-label">Father</span>:<span class="detail-value"><?php echo htmlspecialchars($student['father_name']); ?></span></div>
 
                            <div class="detail-row"><span class="detail-label">Reg. ID</span>:<span class="detail-value"><?php echo htmlspecialchars($student['reg_id']); ?></span></div>
                            <div class="detail-row"><span class="detail-label">Address</span>:<span class="detail-value"><?php echo htmlspecialchars($student['address']); ?></span></div>
                            <!-- <div class="detail-row"><span class="detail-label">Blood</span>:<span class="detail-value"><?php echo htmlspecialchars($student['blood'] != '-' ? $student['blood'] : ''); ?></span></div> -->
                        </div>

                        <div class="footer-contact">
                            <div class="phone-number">
                                <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($school_phone); ?>
                            </div>
                            <div class="principal-signature">
                                <!-- <img src="https://placehold.co/100x30/FFFFFF/999999?text=Signature" alt="Principal Signature"> -->
                                <p>Principal</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="id-card id-card-back">
                        <div class="back-header">
                           
                            <p><?php echo htmlspecialchars($school_name_line_1. ' '.$school_name_line_2); ?></p>
                        </div>
                        
                        <div class="info-block">
                            <p>If found, please return to: <br><?php echo htmlspecialchars($school_name_line_1. ' '.$school_name_line_2).'<BR>'. $school_address; ?>
                        <br><br>Issue Date: <?php echo date("Y-m-d"); ?></p>
                        </div>
                        
                         <div class="qr-signature-block">
                            <div class="qr-code">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode('https://pkrsc.com/admin/1/print.php?search_id=' . $student['id']); ?>" alt="QR Code">
                            </div>
                            
                        </div>
                        <div class="emergency-contact">
                            <h5>Emergency Contact</h5>
                         
                            <p><?php echo htmlspecialchars($school_phone); ?> (School Office)</p>
                            <p><?php echo htmlspecialchars('pkrsc.2018@gmail.com'); ?></p>
                            <p>www.pkrsc.com</p>
                        </div>

                       
                    </div>

                </div> </div> <?php
            $counter++;
            ?>

        <?php endforeach; ?>
        
        </div> <?php else : ?>
        <div class="error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

</body>
</html>