<?php


 function bangla_to_phonetic($text) {
                                    $map = [
                                        'অ'=>'a','আ'=>'a','ই'=>'i','ঈ'=>'ee','উ'=>'u','ঊ'=>'oo','ঋ'=>'ri','এ'=>'e','ঐ'=>'oi','ও'=>'o','ঔ'=>'ou',
                                        'ক'=>'k','খ'=>'kh','গ'=>'g','ঘ'=>'gh','ঙ'=>'ng',
                                        'চ'=>'ch','ছ'=>'chh','জ'=>'j','ঝ'=>'jh','ঞ'=>'n',
                                        'ট'=>'t','ঠ'=>'th','ড'=>'d','ঢ'=>'dh','ণ'=>'n',
                                        'ত'=>'t','থ'=>'th','দ'=>'d','ধ'=>'dh','ন'=>'n',
                                        'প'=>'p','ফ'=>'ph','ব'=>'b','ভ'=>'bh','ম'=>'m',
                                        'য'=>'j','র'=>'r','ল'=>'l','শ'=>'sh','ষ'=>'sh','স'=>'s','হ'=>'h',
                                        'ড়'=>'r','ঢ়'=>'rh','য়'=>'y','ৎ'=>'t','ং'=>'ng','ঃ'=>'h','ঁ'=>'n',
                                        // Vowel signs
                                        'া'=>'a','ি'=>'i','ী'=>'ee','ু'=>'u','ূ'=>'oo','ৃ'=>'ri','ে'=>'e','ৈ'=>'oi','ো'=>'o','ৌ'=>'ou',
                                    ];
                                    $out = '';
                                    $len = mb_strlen($text, 'UTF-8');
                                    for ($i = 0; $i < $len; $i++) {
                                        $ch = mb_substr($text, $i, 1, 'UTF-8');
                                        $out .= isset($map[$ch]) ? $map[$ch] : $ch;
                                    }
                                    return $out;
                                }
require_once '../config.php';
// Example: Get IDs from query string (?id=1,2,3,4,5,6,7,8,9,10,11,12)
$ids = isset($_GET['id']) ? explode(',', $_GET['id']) : [];

// Group IDs into chunks of 9 (for each A4 page)
$idChunks = array_chunk($ids, 9);
?>
<!DOCTYPE html>
<html>
<head>
    <title>ID Cards</title>
    <style>
        @media print {
            .a4-page {
                page-break-after: always;
              
                margin-top: 10px !important;
            }
        }
        .a4-page {
            width: 210mm;
            height: 290mm;
            margin: 5px auto;
          
            padding: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 in a row */
            grid-template-rows: repeat(3, 1fr);   /* 3 rows, total 9 */
            gap: 0;
          
            justify-items: center;
        }
        .id-card {
            height: 85mm;
            width: 52mm;
           
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
          
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
<?php foreach ($idChunks as $chunk): ?>
    <br>
    <div class="a4-page">
        <?php foreach ($chunk as $i => $id): ?>
            <div class="id-card">
            <?php
            // Fetch staff data from database
            $stmt = $conn->prepare("SELECT * FROM staffs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            if ($staff):
            ?>
                <div style="
               
                 height: 85mm;
            width: 52mm;
            border-top: 25px solid #2ecc40;
           
     
           
                ">
                    <!-- Logo -->
                    <div style="text-align:center; margin-top:-40px;">
                        <img src="<?php echo htmlspecialchars($staff['logo_link']); ?>" alt="Logo" style="height:100px;">
                    </div>
                   
                    <!-- Bottom company name (Bangla support) -->
                    <div style="text-align:center; margin-top:-20px; height: 30px; width: 100%;">
                        <span class="fit-text" style="display:block; color:#222; white-space:nowrap; overflow:hidden; width:100%; font-weight:bold; font-size:2em; font-family:'Noto Sans Bengali',
                        <?php
                            // Show status text if not 1, else show Ovijat Group
                            $statusText = 'Ovijat Group';
                            if (isset($staff['status']) && $staff['status'] != 1) {
                                $statusLabels = [
                                    0 => ['Canceled', 'danger'],
                                    1 => ['Active', 'success'],
                                    2 => ['Resign', 'warning'],
                                    3 => ['Dissmiss', 'secondary'],
                                    4 => ['Susspend', 'info'],
                                    5 => ['Hold', 'dark'],
                                    6 => ['Unkown', 'secondary'],
                                ];
                                $status = isset($staff['status']) ? (int)$staff['status'] : 6;
                                $statusText = isset($statusLabels[$status]) ? $statusLabels[$status][0] : htmlspecialchars($staff['status']);
                            }
                        ?>
                        'SolaimanLipi', 'Bangla', 'Arial', sans-serif; line-height:1;"><?php echo $statusText; ?></span>
                    </div>
                    <!-- Google Fonts for Bangla -->
                    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap" rel="stylesheet">
                  
                    <!-- Photo -->
                    <div style="text-align:center; margin-bottom:0px; margin-top:0px; position:relative;">
                        <?php if (!empty($staff['photo_link'])): ?>
                            <img src="<?php echo htmlspecialchars($staff['photo_link']); ?>" alt="Staff Photo" style="height:80px; border-radius:50%; border:2px solid #1a237e; object-fit:cover;">
                        <?php else: ?>
                            <div style="height:80px; background:#e3e7f7; display:flex; align-items:center; justify-content:center; border-radius:50%; border:2px solid #b0bec5; color:#789;">
                                No Photo
                            </div>
                        <?php endif; ?>
                        <?php if (isset($staff['status']) && $staff['status'] != 1): ?>
                            <!-- Big cross overlay -->
                            <div style="position:absolute; top:0; left:50%; transform:translateX(-50%); width:80px; height:80px; pointer-events:none;">
                                <svg width="80" height="80" viewBox="0 0 80 80">
                                    <line x1="10" y1="10" x2="70" y2="70" stroke="red" stroke-width="8" stroke-linecap="round"/>
                                    <line x1="70" y1="10" x2="10" y2="70" stroke="red" stroke-width="8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Staff Info -->
                    <div style="text-align:center; margin-bottom:0px;  width: 100%; ">
                        <strong style="font-size:1.2em; color:#1a237e;"><?php echo htmlspecialchars($staff['name']); ?></strong><br>
                        <span style="font-size:0.95em; color:#333;">
                            Employee ID: 
                            <?php
                                // Helper: Simple Bangla to English phonetic mapping (very basic, not full Avro)
                                // Get first character of company_name, department, section (if not empty)
                                $abbr = '';
                                if (!empty($staff['company_name'])) $abbr .= mb_substr($staff['company_name'], 0, 1, 'UTF-8');
                                if (!empty($staff['department'])) $abbr .= mb_substr($staff['department'], 0, 1, 'UTF-8');
                                if (!empty($staff['section'])) $abbr .= mb_substr($staff['section'], 0, 1, 'UTF-8');

                                // If abbreviation contains Bangla, show phonetic English instead
                                if ($abbr && preg_match('/[\x{0980}-\x{09FF}]/u', $abbr)) {
                                    $phonetic = strtoupper(bangla_to_phonetic($abbr));
                                    // Remove any character except uppercase A-Z
                                    $phonetic = preg_replace('/[^A-Z]/', '', $phonetic);
                                    echo ' <span>' . ($phonetic !== '' ? htmlspecialchars($phonetic) : '-') . '</span>';
                                } elseif ($abbr) {
                                    $abbr_upper = mb_strtoupper($abbr, 'UTF-8');
                                    // Remove any character except uppercase A-Z
                                    $abbr_upper = preg_replace('/[^A-Z]/u', '', $abbr_upper);
                                    echo ' <span>' . ($abbr_upper !== '' ? htmlspecialchars($abbr_upper) : '-') . '</span>';
                                } else {
                                    echo ' <span>-</span>';
                                }
                            ?>
                            <b>- <?php echo str_pad(htmlspecialchars($staff['id']), 7, '0', STR_PAD_LEFT); ?></b>
                        </span>
                    </div>
                    <div style="text-align:center; font-size:0.92em; color:#444;">
                        <span style="display:block; margin-bottom:2px;"><b><?php echo htmlspecialchars($staff['position']); ?></b></span>
                        <?php if (!empty(trim($staff['department'])) && preg_match('/\w/u', $staff['department'])): ?>
                            <span style="display:block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><b><?php echo htmlspecialchars($staff['department']); ?>
                            <?php if (!empty(trim($staff['section'])) && preg_match('/\w/u', $staff['section'])): ?> <?php echo htmlspecialchars($staff['section']); ?><?php endif; ?></b></span>
                        <?php endif; ?>
                        <?php if (!empty(trim($staff['phone'])) && preg_match('/\w/u', $staff['phone'])): ?>
                            <span style="display:block;">Phone: <b><?php echo htmlspecialchars($staff['phone']); ?></b></span>
                        <?php endif; ?>
                        <!-- Centered joining/exist date -->
                        <span style="display:block; margin-top:4px;">
                            <?php if (isset($staff['status']) && $staff['status'] == 1): ?>
                                Joining Date: <b><?php echo htmlspecialchars($staff['joining_date']); ?></b>
                            <?php else: ?>
                                Exit Date: <b style="color:red;"><?php echo htmlspecialchars($staff['exit_date']); ?></b>
                            <?php endif; ?>
                        </span>
                    </div>
 <div style="background: green; color: white; text-align: center;   font-size: 1.3em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 15px;">
                                    Hotline: +880 9647 000025
                                </div>

                                <div style="background: red; color: #fff; text-align: center; padding: 2px 0;   font-size: 0.95em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($staff['company_name']); ?>
                                </div>


                  
                </div>
            <?php
            else:
                echo "Staff not found";
            endif;
            ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
</body>
</html>



<!DOCTYPE html>
<html>
<head>
    <title>ID Cards</title>
    <style>
        @media print {
            .a4-page2 {
                page-break-after: always;
                margin-top: 10px !important;
            }
        }
        .a4-page2 {
            width: 210mm;
            height: 290mm;
            margin: 5px auto;
           
           
            padding: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 in a row */
            grid-template-rows: repeat(3, 1fr);   /* 3 rows, total 9 */
            gap: 0;
           
            justify-items: center;
            direction: rtl; /* Right-to-left grid order */
        }
        .id-card2 {
            height: 85mm;
            width: 52mm;
            
            
            display: flex;
            align-items: center;
            justify-content: center;
            
           
            margin-left: 10px;
            margin-right: auto;
            direction: ltr; /* Force left-to-right content direction */
        }
      
        /* No column reversal, keep normal left-to-right order */
    </style>
</head>
<body>
<?php foreach ($idChunks as $chunk): ?>
    <br>
    <div class="a4-page2">
        <?php foreach ($chunk as $i => $id): ?>
            <div class="id-card2">
                 <?php
            // Fetch staff data from database
            $stmt = $conn->prepare("SELECT * FROM staffs WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            if ($staff):
            ?>
                <div style="
              
                 height: 85mm;
            width: 52mm;
                
                ">
                    <!-- Logo -->
                    <div style="text-align:center; margin-bottom:0px;">
                        <!-- Online free QR code for current URL -->
                    <?php
                        // Generate QR code for base URL with staff id and id=i
                        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/verify/?id=" . urlencode($id);
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=" . urlencode($base_url);
                    ?>
                
                    <img src="<?php echo $qr_url; ?>" alt="QR Code" style="height:100px; width:100px; margin-top:4px;">
                   
                
                        <br>
                        <span style="font-size:1.5em; color:#222;">
                            Ovijat Group
                        </span>
                    </div>
                   
                   
                    <!-- other Info -->
                    <div style="margin-top:15px; text-align:center; margin-bottom:6px; font-size:calc(0.45em + 0.25vw); color:#222; line-height:1.25; max-width:100%; word-break:break-word;">
                        <div style="font-weight:bold; color:#c0392b; margin-bottom:10px; font-size:1.2em;">
                            Please return this card, if found.
                        </div>
                        <div style="margin-bottom:4px;">
                            <span style="font-weight:bold;">Head Office:</span>
                            Sadharan Bima Bhaban 2,
                            139, Motijheel C/A, Dhaka-1000, Bangladesh
                        </div>
                        <div style="margin-bottom:4px;">
                            <span style="font-weight:bold;">USA Office:</span>
                            Delight Distribution Inc. 
                            5605 Maspeth, New York, USA
                        </div>
                        <div style="margin-bottom:4px;">
                            <span style="font-weight:bold;">Factory:</span> Ramganj, Nilphamari, Bangladesh<br>
                            
                        </div>
                        <div style=" font-weight:bold; padding:2px 0; margin:6px 0 2px 0; border-radius:2px; font-size:1.1em;">
                            Scan to verify this card. 
                        </div>
                        <div style="">
                            <span style="font-weight:bold;"></span> (+88) 02 951 3985 / (+88) 0173 339 0331
                        </div>
                        <div>
                            <span style="font-weight:bold;">Email:</span> info@ovijatfood.com
                        </div>

                     <div style=" font-weight:bold; padding:2px 0;   font-size:0.8em; margin-top:12px;">
                            Developed by IT Department, Ovijat Group
                        </div>
                    </div>
                  
                </div>
            <?php
            else:
                echo "Staff not found";
            endif;
            ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
</body>
</html>