<?php
session_start();
require_once 'config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$userId = $_SESSION['user_id'];

// Function to handle CSV import (from file or string)
function importCsvData($tableName, $dataHandle, $conn, $userId) {
    global $message;
    $importedCount = 0;
    $skippedCount = 0;
    $errors = [];

    $header = fgetcsv($dataHandle); // Read header row
    if ($header === FALSE) {
        $message = "Error reading CSV header or empty CSV.";
        return false;
    }

    while (($data = fgetcsv($dataHandle)) !== FALSE) {
        if (count($header) !== count($data)) {
            $errors[] = "Skipping row due to column mismatch: " . implode(",", $data);
            $skippedCount++;
            continue;
        }
        $row = array_combine($header, $data);

        try {
            switch ($tableName) {
                case 'nlu_data':
                    if (isset($row['input_text']) && isset($row['intent'])) {
                        $stmt = $conn->prepare("INSERT INTO nlu_data (user_id, input_text, intent, entities, sentiment, confidence) VALUES (?, ?, ?, ?, ?, ?)");
                        $entities = isset($row['entities']) ? $row['entities'] : '';
                        $sentiment = isset($row['sentiment']) ? $row['sentiment'] : '';
                        $confidence = isset($row['confidence']) ? (float)$row['confidence'] : 0.0;
                        $stmt->bind_param("issssd", $userId, $row['input_text'], $row['intent'], $entities, $sentiment, $confidence);
                        $stmt->execute();
                        $importedCount++;
                    } else {
                        $errors[] = "Skipping nlu_data row: missing input_text or intent. Data: " . implode(",", $data);
                        $skippedCount++;
                    }
                    break;
                case 'decision_paths':
                    if (isset($row['rule_name']) && isset($row['conditions']) && isset($row['action'])) {
                        $stmt = $conn->prepare("INSERT INTO decision_paths (rule_name, conditions, action) VALUES (?, ?, ?)");
                        $stmt->bind_param("sss", $row['rule_name'], $row['conditions'], $row['action']);
                        $stmt->execute();
                        $importedCount++;
                    } else {
                        $errors[] = "Skipping decision_paths row: missing rule_name, conditions, or action. Data: " . implode(",", $data);
                        $skippedCount++;
                    }
                    break;
                case 'story_blocks':
                    if (isset($row['block_name']) && isset($row['template'])) {
                        $stmt = $conn->prepare("INSERT INTO story_blocks (block_name, template) VALUES (?, ?) ON DUPLICATE KEY UPDATE template = VALUES(template)");
                        $stmt->bind_param("ss", $row['block_name'], $row['template']);
                        $stmt->execute();
                        $importedCount++;
                    } else {
                        $errors[] = "Skipping story_blocks row: missing block_name or template. Data: " . implode(",", $data);
                        $skippedCount++;
                    }
                    break;
                case 'training_data':
                    if (isset($row['input_text']) && isset($row['corrected_intent'])) {
                        $stmt = $conn->prepare("INSERT INTO training_data (user_id, input_text, corrected_intent) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE corrected_intent = VALUES(corrected_intent)");
                        $stmt->bind_param("iss", $userId, $row['input_text'], $row['corrected_intent']);
                        $stmt->execute();
                        $importedCount++;
                    } else {
                        $errors[] = "Skipping training_data row: missing input_text or corrected_intent. Data: " . implode(",", $data);
                        $skippedCount++;
                    }
                    break;
                default:
                    $errors[] = "Unknown table name: " . $tableName;
                    $skippedCount++;
                    break 2;
            }
            if ($stmt->error) {
                $errors[] = "DB Error for row " . implode(",", $data) . ": " . $stmt->error;
                $skippedCount++;
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Exception for row " . implode(",", $data) . ": " . $e->getMessage();
            $skippedCount++;
        }
    }
    fclose($dataHandle);

    $message = "Import complete for {$tableName}: {$importedCount} rows imported, {$skippedCount} rows skipped.";
    if (!empty($errors)) {
        $message .= "\nErrors: " . implode("\n", $errors);
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_single_entry'])) {
        $trigger = trim($_POST['trigger_phrase']);
        $response = trim($_POST['response']);

        if (!empty($trigger) && !empty($response)) {
            $stmt = $conn->prepare("INSERT INTO training_data (user_id, input_text, corrected_intent) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE corrected_intent = ?");
            $stmt->bind_param("isss", $userId, $trigger, $response, $response);
            if ($stmt->execute()) {
                $message = "Single training entry saved successfully!";
            } else {
                $message = "Error saving single training entry: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Trigger phrase and response cannot be empty.";
        }
    } elseif (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $tableName = $_POST['table_name_file']; // Changed name to avoid conflict
        $file = $_FILES['csv_file'];
        $handle = fopen($file['tmp_name'], "r");
        if ($handle) {
            importCsvData($tableName, $handle, $conn, $userId);
        } else {
            $message = "Error opening uploaded file.";
        }
    } elseif (isset($_POST['paste_csv_data'])) {
        $tableName = $_POST['table_name_paste']; // Changed name to avoid conflict
        $csvData = trim($_POST['csv_data']);

        if (!empty($csvData)) {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, $csvData);
            rewind($handle);
            importCsvData($tableName, $handle, $conn, $userId);
        } else {
            $message = "Pasted CSV data cannot be empty.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Simple AI</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #1a1a2e;
            color: #00ff00;
            font-family: 'Cascadia Code', 'Consolas', monospace;
            font-size: 16px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: auto; /* Changed to auto for scrollability */
        }
        #admin-container {
            background-color: #0d0d1a;
            border: 1px solid #00ff00;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
            width: 90%;
            max-width: 900px;
            padding: 20px;
            box-sizing: border-box;
            margin: 20px 0; /* Added margin for better spacing */
        }
        #admin-container h2 {
            color: #00ffff;
            text-align: center;
            margin-bottom: 20px;
        }
        #admin-container form {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
            border-bottom: 1px solid #00ff00;
            padding-bottom: 20px;
        }
        #admin-container label {
            margin-bottom: 5px;
            color: #00ff00;
        }
        #admin-container input[type="text"],
        #admin-container input[type="password"],
        #admin-container select,
        #admin-container input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #00ff00;
            border-radius: 4px;
            background-color: #1e1e1e;
            color: #00ff00;
            font-family: inherit;
            font-size: inherit;
        }
        #admin-container textarea {
            background-color: #1e1e1e;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px;
            margin-bottom: 15px;
            font-family: inherit;
            font-size: inherit;
            resize: vertical;
            min-height: 80px;
        }
        #admin-container button {
            background-color: #005500;
            color: #00ff00;
            border: 1px solid #00ff00;
            padding: 10px 15px;
            cursor: pointer;
            font-family: inherit;
            font-size: 16px;
            transition: background-color 0.2s, color 0.2s;
            margin-top: 10px;
        }
        #admin-container button:hover {
            background-color: #00ff00;
            color: #0d0d1a;
        }
        #message {
            color: #00ffff;
            text-align: center;
            margin-top: 15px;
            white-space: pre-wrap; /* To display newlines in messages */
        }
        .sample-data-section {
            margin-top: 30px;
            border-top: 1px solid #00ff00;
            padding-top: 20px;
        }
        .sample-data-section h3 {
            color: #00ffff;
            margin-bottom: 10px;
        }
        .sample-data-section pre {
            background-color: #1e1e1e;
            border: 1px solid #00ff00;
            padding: 10px;
            overflow-x: auto;
            margin-bottom: 20px;
            color: #00ff00;
        }
        #back-to-chat {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #00ffff;
            text-decoration: none;
        }
        #back-to-chat:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="admin-container">
        <h2>Admin Panel - Manage Training Data</h2>

        <!-- Single Entry Form -->
        <form method="POST" action="admin.php">
            <h3>Add Single Training Entry</h3>
            <label for="trigger_phrase">Trigger Phrase:</label>
            <textarea id="trigger_phrase" name="trigger_phrase" placeholder="e.g., what is the capital of france" required></textarea>
            
            <label for="response">Response:</label>
            <textarea id="response" name="response" placeholder="e.g., The capital of France is Paris." required></textarea>
            
            <button type="submit" name="add_single_entry">Save Single Entry</button>
        </form>

        <!-- CSV File Upload Form -->
        <form method="POST" action="admin.php" enctype="multipart/form-data">
            <h3>Import Data from CSV File</h3>
            <label for="table_name_file">Select Table:</label>
            <select id="table_name_file" name="table_name_file" required>
                <option value="">-- Select a table --</option>
                <option value="nlu_data">nlu_data</option>
                <option value="decision_paths">decision_paths</option>
                <option value="story_blocks">story_blocks</option>
                <option value="training_data">training_data</option>
            </select>
            
            <label for="csv_file">Upload CSV File:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            
            <button type="submit" name="upload_csv">Import CSV File</button>
        </form>

        <!-- Paste CSV Data Form -->
        <form method="POST" action="admin.php">
            <h3>Paste CSV Data</h3>
            <label for="table_name_paste">Select Table:</label>
            <select id="table_name_paste" name="table_name_paste" required>
                <option value="">-- Select a table --</option>
                <option value="nlu_data">nlu_data</option>
                <option value="decision_paths">decision_paths</option>
                <option value="story_blocks">story_blocks</option>
                <option value="training_data">training_data</option>
            </select>
            
            <label for="csv_data">Paste CSV Data Here:</label>
            <textarea id="csv_data" name="csv_data" placeholder="Paste your CSV data here, including headers." required></textarea>
            
            <button type="submit" name="paste_csv_data">Import Pasted CSV</button>
        </form>

        <?php if ($message): ?>
            <p id="message"><?= nl2br(htmlspecialchars($message)) ?></p>
        <?php endif; ?>

        <div class="sample-data-section">
            <h3>Sample CSV Formats</h3>

            <h4>nlu_data.csv</h4>
            <pre>input_text,intent,entities,sentiment,confidence
what is the weather,get_weather,{},neutral,0.8
tell me a joke,tell_joke,{},positive,0.9</pre>

            <h4>decision_paths.csv</h4>
            <pre>rule_name,conditions,action
weather_sunny,{"weather":"sunny"},"It's a beautiful day!"
weather_rainy,{"weather":"rainy"},"Don't forget your umbrella!"</pre>

            <h4>story_blocks.csv</h4>
            <pre>block_name,template
fantasy_adventure,"Once upon a time, a {hero} fought a {villain} in {setting}."
sci_fi_tale,"In the year {year}, a {character} explored {planet}."</pre>

            <h4>training_data.csv</h4>
            <pre>input_text,corrected_intent
hi bot,greeting
what is 2 plus 2,The answer is 4.</pre>
        </div>

        <a href="index.html" id="back-to-chat">Back to Chat</a>
    </div>
</body>
</html>