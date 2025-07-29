<?php
session_start();

require_once 'config.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    // For AJAX requests, send a header indicating authentication required
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('HTTP/1.0 401 Unauthorized');
        echo "Unauthorized";
        exit();
    } else {
        header('Location: login.php');
        exit();
    }
}

// Establish database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Helper Functions ---
function setMemory($key, $value, $userId, $conn) {
    $stmt = $conn->prepare("INSERT INTO user_memory (user_id, memory_key, memory_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE memory_value = ?");
    $stmt->bind_param("isss", $userId, $key, $value, $value);
    $stmt->execute();
    $stmt->close();
}

function getMemory($key, $userId, $conn) {
    $stmt = $conn->prepare("SELECT memory_value FROM user_memory WHERE user_id = ? AND memory_key = ?");
    $stmt->bind_param("is", $userId, $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['memory_value'];
    }
    $stmt->close();
    return null;
}

function saveTrainingData($trigger, $response, $userId, $conn) {
    $stmt = $conn->prepare("INSERT INTO training_data (user_id, input_text, corrected_intent) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE corrected_intent = ?");
    $stmt->bind_param("isss", $userId, $trigger, $response, $response);
    $stmt->execute();
    $stmt->close();
}

function getLearnedResponse($userInput, $userId, $conn) {
    $stmt = $conn->prepare("SELECT corrected_intent FROM training_data WHERE user_id = ? AND LOWER(input_text) = ?");
    $stmt->bind_param("is", $userId, $userInput);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['corrected_intent'];
    }
    $stmt->close();
    return null;
}

function safe_eval($expression) {
    // Replace any non-mathematical characters with a space.
    $sanitized_expression = preg_replace('/[^0-9\.\+\-\*\/\(\)\s]/', ' ', $expression);

    // If the string is empty or just whitespace after sanitizing, it's invalid.
    if (trim($sanitized_expression) === '') {
        return null;
    }
    
    // Check for invalid patterns, like multiple operators together.
    if (preg_match('/[\+\-\*\/]{2,}/', $sanitized_expression)) {
        return null;
    }

    // Use a custom error handler to safely catch parsing errors from eval().
    try {
        set_error_handler(function($errno, $errstr) {
            throw new ErrorException($errstr, $errno);
        });
        $result = eval("return ($sanitized_expression);");
        restore_error_handler();
        return $result;
    } catch (Throwable $e) {
        restore_error_handler();
        return null;
    }
}

function performWebSearch($query, $userId, $conn) {
    // Aggressively clean the query for better Wikipedia search results
    $cleanedQuery = preg_replace('/[^a-zA-Z0-9\s]/', '', $query); // Remove all punctuation
    $searchQuery = urlencode(trim($cleanedQuery));

    // Check if allow_url_fopen is enabled
    if (!ini_get('allow_url_fopen')) {
        return "Sorry, I cannot perform web searches. 'allow_url_fopen' is disabled in your PHP configuration.";
    }

    // Step 1: Use opensearch to find relevant page titles
    $opensearchUrl = "https://en.wikipedia.org/w/api.php?action=opensearch&format=json&search=" . $searchQuery . "&limit=1";
    error_log("Opensearch URL: " . $opensearchUrl);
    $opensearchResponse = @file_get_contents($opensearchUrl);
    error_log("Opensearch Raw Response: " . ($opensearchResponse === FALSE ? "FALSE" : $opensearchResponse));

    if ($opensearchResponse === FALSE) {
        return "Sorry, I'm having trouble connecting to the web. Do you want to set any data related to this?";
    }

    $opensearchData = json_decode($opensearchResponse, true);
    $pageTitle = null;
    if (isset($opensearchData[1][0])) {
        $pageTitle = $opensearchData[1][0];
    }

    if ($pageTitle) {
        // Step 2: Fetch extract using the found page title
        $extractUrl = "https://en.wikipedia.org/w/api.php?action=query&format=json&prop=extracts&exintro=true&explaintext=true&redirects=1&titles=" . urlencode($pageTitle);
        error_log("Extract URL: " . $extractUrl);
        $extractResponse = @file_get_contents($extractUrl);
        error_log("Extract Raw Response: " . ($extractResponse === FALSE ? "FALSE" : $extractResponse));

        if ($extractResponse === FALSE) {
            return "Sorry, I'm having trouble connecting to the web. Do you want to set any data related to this?";
        }

        $extractData = json_decode($extractResponse, true);

        if (isset($extractData['query']['pages'])) {
            $page = reset($extractData['query']['pages']);
            if (isset($page['extract']) && is_string($page['extract'])) {
                $extract = $page['extract'];
                // Remove null bytes, control characters, and normalize whitespace
                $extract = str_replace("\0", "", $extract);
                $extract = preg_replace('/[\p{C}]/u', '', $extract); // Remove all Unicode control characters
                $extract = preg_replace('/\s+/', ' ', $extract); // Replace multiple spaces with single space
                $extract = trim($extract);

                if (strlen($extract) > 0) {
                    // Automatically save successful web search results to user_memory
                    setMemory(strtolower(trim($query, "?.! ")), $extract, $userId, $conn);
                    return $extract;
                }
            }
        }
    }
    
    return "Sorry, I'm not getting any data for \"" . htmlspecialchars($query) . "\". Do you want to set any data related to this?";
}

function generateStory($conn, $entities) {
    // Fetch a random story template
    $stmt = $conn->prepare("SELECT template FROM story_blocks ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $template = "";
    if ($row = $result->fetch_assoc()) {
        $template = $row['template'];
    }
    $stmt->close();

    if (empty($template)) {
        return "I'm sorry, I don't have any stories to tell right now.";
    }

    // Identify placeholders (e.g., {hero}, {villain})
    preg_match_all('/\{([^\}]+)\}/', $template, $matches);
    $placeholders = $matches[1];

    // Fill placeholders
    foreach ($placeholders as $placeholder) {
        $replacement = "a " . str_replace('_', ' ', $placeholder); // Default generic replacement
        // You could add more sophisticated logic here to use entities or ask the user for input
        $template = str_replace('{' . $placeholder . '}', $replacement, $template);
    }

    return $template;
}

// --- Main NLU and Response Logic ---
function analyzeNLU($userInput, $conn) {
    $originalUserInput = $userInput;
    $userInputLower = strtolower(trim($userInput, "?.! "));
    $intent = 'unknown';
    $entities = [];
    $confidence = 0.0;

    $userId = $_SESSION['user_id'];
    // Removed: $lastSubject = getMemory('_last_subject', $userId, $conn);

    // --- Prioritize exact matches for affirmative/negative ---
    if (in_array($userInputLower, ['yes', 'y', 'yeah', 'yep', 'ok', 'okay'])) {
        $intent = 'affirmative';
        $confidence = 1.0;
    } elseif (in_array($userInputLower, ['no', 'n', 'nope', 'nah'])) {
        $intent = 'negative';
        $confidence = 1.0;
    } 
    // --- End Prioritization ---

    // 1. Check for keyword-based intents (only if intent is still unknown)
    if ($intent === 'unknown') {
        $intents = [
            'greeting' => ['hello', 'hi', 'hey'],
            'goodbye' => ['bye', 'goodbye', 'see you'],
            'get_time' => ['time', 'what time is it'],
            'set_memory' => ['my name is', 'remember my favorite color is', 'my favorite food is'],
            'get_memory' => ['what is my name', 'what is my favorite color', 'what is my favorite food'],
            'teach' => ['if i say', 'when i say'],
            'story' => ['tell me a story', 'write a story', 'generate a story']
        ];

        foreach ($intents as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($userInputLower, $keyword) !== false) {
                    $intent = $key;
                    $confidence = 1.0;

                    if ($intent === 'set_memory') {
                        $parts = explode($keyword, $originalUserInput);
                        $value = trim(end($parts), "?.! ");
                        $memoryKey = str_replace(['my ', ' is', ' favorite'], '', $keyword);
                        $entities['memory_key'] = trim(str_replace(' ', '_', $memoryKey));
                        $entities['memory_value'] = $value;
                    } elseif ($intent === 'teach') {
                        $separator = 'you should say';
                        $parts = explode($separator, $originalUserInput, 2);
                        if (count($parts) === 2) {
                            if (preg_match("/'(.*?)'/", $parts[0], $triggerMatches)) {
                                $entities['trigger'] = trim($triggerMatches[1], "?.!, ");
                            }
                            if (preg_match("/'(.*?)'/", $parts[1], $responseMatches)) {
                                $entities['response'] = trim($responseMatches[1]);
                            }
                        }
                    }
                    break 2;
                }
            }
        }
    }

    // 2. If no keyword intent, check if it's a mathematical expression
    if ($intent === 'unknown') {
        // Remove spaces and punctuation for a stricter math check
        $strippedInput = str_replace(' ', '', $userInputLower);
        $strippedInput = trim($strippedInput, "?.!= "); // Remove common non-math ending chars

        // Check if it contains at least one digit and at least one operator
        // And ensure it primarily consists of math characters
        if (preg_match('/[0-9]/', $strippedInput) && preg_match('/[\+\-\*\/]/', $strippedInput) && preg_match('/^[0-9\.\+\-\*\/\(\)]+$/', $strippedInput)) {
            $intent = 'calculate';
            $entities['expression'] = $originalUserInput; // Keep original for safe_eval
            $confidence = 0.9;
        }
    }

    // 3. If still unknown, set intent to search_web
    if ($intent === 'unknown') {
        $intent = 'search_web';
        // Clean the query for better Wikipedia search results
        $cleanedSearchQuery = preg_replace('/^(what is|tell me about|who is|where is|when is|how is|why is)\s+/i', '', $originalUserInput);
        $entities['query'] = trim($cleanedSearchQuery);
        $confidence = 0.5; // Lower confidence for a general search fallback
    }

    // Store NLU data
    $stmt = $conn->prepare("INSERT INTO nlu_data (user_id, input_text, intent, entities, confidence) VALUES (?, ?, ?, ?, ?)");
    $userId = $_SESSION['user_id'];
    $entitiesJson = json_encode($entities);
    $stmt->bind_param("isssd", $userId, $originalUserInput, $intent, $entitiesJson, $confidence);
    $stmt->execute();
    $stmt->close();

    return ['intent' => $intent, 'entities' => $entities, 'confidence' => $confidence];
}

function getResponse($userInput, $conn) {
    $userId = $_SESSION['user_id'];

    $cleanedInput = strtolower(trim($userInput, "?.! "));
    $learnedResponse = getLearnedResponse($cleanedInput, $userId, $conn);
    if ($learnedResponse) {
        return $learnedResponse;
    }

    // Check user_memory for a direct answer to the cleaned input
    $directMemoryResponse = getMemory($cleanedInput, $userId, $conn);
    if ($directMemoryResponse) {
        return $directMemoryResponse;
    }

    $nluResult = analyzeNLU($userInput, $conn);
    $intent = $nluResult['intent'];
    $entities = $nluResult['entities'];

    // Removed: $waitingForDataQuery = getMemory('_waiting_for_data_for_query', $userId, $conn);
    // Removed: $expectingDataForQuery = getMemory('_expecting_data_for_query', $userId, $conn);

    // Removed: Multi-turn data input logic

    switch ($intent) {
        case 'greeting':
            $name = getMemory('name', $userId, $conn);
            return $name ? "Hello, " . ucfirst($name) . "!" : "Hello there!";
        case 'goodbye':
            return "Goodbye! Have a great day.";
        case 'get_time':
            return "The current time is " . date("h:i A");
        case 'set_memory':
            setMemory($entities['memory_key'], $entities['memory_value'], $userId, $conn);
            return "Okay, I will remember that.";
        case 'get_memory':
            $memoryKey = str_replace(['what is my ', 'what is my favorite ', '?'], '', strtolower($userInput));
            $memoryKey = trim(str_replace(' ', '_', $memoryKey));
            $value = getMemory($memoryKey, $userId, $conn);
            return $value ? "Your " . str_replace('_', ' ', $memoryKey) . " is " . $value . "." : "I don't have that information stored.";
        case 'teach':
            if (isset($entities['trigger']) && isset($entities['response'])) {
                saveTrainingData($entities['trigger'], $entities['response'], $userId, $conn);
                return "Okay, I've learned that.";
            } else {
                return "I didn't quite understand that. Please use the format: If I say 'your phrase', you should say 'my desired response'.";
            }
        case 'calculate':
            if (isset($entities['expression'])) {
                $result = safe_eval($entities['expression']);
                return $result !== null ? "The result is " . $result . "." : "I can only perform basic calculations.";
            }
            else {
                return "I didn't understand the calculation.";
            }
        case 'search_web':
            $response = performWebSearch($entities['query'], $userId, $conn); // Pass $userId and $conn
            // Removed: If web search was successful, store the query as the last subject
            // Removed: If web search failed, clear the last subject and set a flag for data input
            return $response;
        case 'story':
            return generateStory($conn, $entities);
        default:
            return "I'm not sure how to respond to that. Can you try asking in a different way?";
    }
}

// --- Main execution block ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = isset($_POST['input']) ? trim($_POST['input']) : '';
    $userId = $_SESSION['user_id'];

    if (!empty($input)) {
        // Store conversation
        $stmt = $conn->prepare("INSERT INTO conversation_history (user_id, user_input) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $input);
        $stmt->execute();
        $stmt->close();

        $response = getResponse($input, $conn);

        // Update conversation with bot response
        $lastId = $conn->insert_id;
        $stmt = $conn->prepare("UPDATE conversation_history SET bot_response = ? WHERE id = ?");
        $stmt->bind_param("si", $response, $lastId);
        $stmt->execute();
        $stmt->close();

        try {
            echo $response;
        } catch (Throwable $e) {
            error_log("Error echoing response: " . $e->getMessage());
            echo "Simple AI: An internal error occurred while generating the response.";
        }
    }
}

$conn->close();

?>