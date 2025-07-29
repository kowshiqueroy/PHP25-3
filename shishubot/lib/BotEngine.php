<?php

require_once 'KnowledgeManager.php';
require_once 'TaskEngine.php';
require_once 'GrammarEngine.php';

class BotEngine {
    private array $intents = [
        'math' => ['solve', 'equation', 'math', 'what is', 'calculate', 'eval', 'compute', 'result of'],
        'calculate' => ['calculate', 'sum', 'average', 'count', 'total', 'mean'],
        'sort' => ['sort', 'order', 'arrange', 'list'],
        'code_generate' => ['write code', 'generate code', 'code for', 'create a function', 'php function', 'javascript function', 'python script', 'c function'],
        'code_review' => ['review code', 'debug code', 'fix code', 'improve code'],
        'creative' => ['write a poem', 'tell a story', 'motivational quote', 'metaphor', 'generate poem', 'generate story', 'generate quote', 'generate metaphor', 'poem', 'story', 'quote', 'metaphor'],
        'teach' => ['teach', 'remember', 'learn me', 'store'],
        'query' => ['tell me about', 'define', 'explain', 'who is', 'where is', 'tell me more', 'what about its relationships'],
        'name' => ['what is your name', 'who are you'],
        'greet' => ['hello', 'hi', 'hey'],
        'mood' => ['how are you', 'how do you do'],
        'filter' => ['filter', 'select from'],
    ];

    private KnowledgeManager $knowledgeManager;
    private TaskEngine $taskEngine;
    private GrammarEngine $grammarEngine;
    private int $userId;

    public function __construct(PDO $pdo, int $userId) {
        $this->knowledgeManager = new KnowledgeManager($pdo);
        $this->taskEngine = new TaskEngine();
        $this->grammarEngine = new GrammarEngine();
        $this->userId = $userId;
    }

    public function processMessage(string $message): string {
        $originalMessage = $message;
        $preprocessedResult = $this->grammarEngine->preprocessMessage($originalMessage);
        $preprocessedMessage = $preprocessedResult['message'];
        $correctionsMade = $preprocessedResult['corrections'];

        $intent = $this->detectIntent($preprocessedMessage); // Use preprocessed message for intent detection

        $botResponse = "";
        $resolvedQuestions = [];

        switch ($intent) {
            case 'greet':
                $botResponse = "Hello there! How can I help you today?";
                break;
            case 'mood':
                $botResponse = "I am just a bot, but I am functioning well. Thanks for asking!";
                break;
            case 'name':
                $botResponse = "I am shishuBot, your personal intelligent assistant.";
                break;
            case 'teach':
                $result = $this->handleTeachIntent($preprocessedMessage);
                $botResponse = $result['response'];
                $resolvedQuestions = $result['resolved_questions'];
                break;
            case 'teach_relationship':
                $botResponse = $this->handleTeachRelationshipIntent($originalMessage); // Use original message for relationship parsing
                break;
            case 'query':
                $botResponse = $this->handleQueryIntent($preprocessedMessage); // Pass preprocessed message to query handler
                break;
            case 'code_generate':
                $botResponse = "I can review code. Please provide the code snippet you'd like me to analyze.";
                break;
            case 'code_review':
                $botResponse = "I can review code. Please provide the code snippet you'd like me to analyze.";
                break;
            case 'sort':
                $botResponse = $this->handleSortIntent($preprocessedMessage);
                break;
            case 'calculate':
                $botResponse = $this->handleCalculateIntent($originalMessage);
                break;
            case 'filter':
                $botResponse = "I can filter datasets. Please provide the data and your criteria.";
                break;
            case 'math':
                $botResponse = $this->handleMathIntent($originalMessage);
                break;
            case 'creative':
                $botResponse = $this->handleCreativeIntent($preprocessedMessage);
                break;
            case 'show_deferred_questions':
                $botResponse = $this->handleShowDeferredQuestionsIntent();
                break;
            default:
                $botResponse = "I'm not sure how to respond to that yet. Can you rephrase or ask something else?";
                break;
        }

        // Append correction suggestions if any were made
        if (!empty($correctionsMade)) {
            $correctionStrings = [];
            foreach ($correctionsMade as $typo => $correction) {
                $correctionStrings[] = "'{$typo}' to '{$correction}'";
            }
            $botResponse .= "\n\n(I understood your message by correcting: " . implode(", ", $correctionStrings) . ")";
        }

        return [
            'response' => $botResponse,
            'resolved_questions' => $resolvedQuestions
        ];
    }

    private function detectIntent(string $message): ?string {
        // 1. Prioritize math expressions with a robust regex
        if (preg_match('/^(?:what is|calculate|eval|compute|result of)?\s*([0-9+\-*\/%.()\s]+)$/i', $message)) {
            return 'math';
        }

        // 2. Prioritize teach_relationship
        if (preg_match('/^(?:remember that|teach me that)\s+(.+?)\s+(is a type of|has property|is part of|is near)\s+(.+)$/i', $message)) {
            return 'teach_relationship';
        }

        // 3. Then check other intents using str_contains on the preprocessed message
        foreach ($this->intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intent;
                }
            }
        }
        return null;
    }

    private function handleTeachIntent(string $message): array {
        // Expected format: "remember that [entity] is [definition]"
        // Or: "teach me that [entity] is [definition]" (after preprocessing)
        $pattern = '/^(?:teach me that|remember that)\s+(.+?)\s+is\s+(.+)$/i';
        $response = "";
        $resolvedQuestions = [];

        if (preg_match($pattern, $message, $matches)) {
            $entity = rtrim(trim($matches[1]), '?'); // Trim whitespace and trailing question mark
            $definition = trim($matches[2]);

            if ($this->knowledgeManager->addKnowledge($entity, $definition, $this->userId)) {
                $response = "Okay, I've remembered that '{$entity}' is '{$definition}'.";

                // Check for and resolve deferred questions related to this entity
                $pendingQuestions = $this->knowledgeManager->getPendingDeferredQuestionsByEntity($entity);
                if (!empty($pendingQuestions)) {
                    $response .= "\n\nI also had some pending questions about '{$entity}':";
                    foreach ($pendingQuestions as $q) {
                        $this->knowledgeManager->markDeferredQuestionAsResolved($q['id']);
                        $resolvedQuestions[] = [
                            'question_text' => $q['question_text'],
                            'definition' => $definition
                        ];
                    }
                }
            } else {
                $response = "I had trouble remembering that. Please try again.";
            }
        }
        return [
            'response' => $response,
            'resolved_questions' => $resolvedQuestions
        ];
    }

    private function handleQueryIntent(string $message): string {
        // Expected format: "what is [entity]" or "tell me about [entity]"
        $pattern = '/^(what is|tell me about|define|explain|who is|where is)\s+(.*?)$/i';
        $follow_up_pattern = '/^(tell me more|what about its relationships)$/i';

        $entity = null;
        $query_text = $message; // Store original query text for deferred question

        if (preg_match($pattern, $message, $matches)) {
            $entity = rtrim(trim($matches[2]), '?'); // Trim whitespace and trailing question mark
            $_SESSION['last_entity'] = $entity; // Store the last queried entity
        } elseif (preg_match($follow_up_pattern, $message)) {
            if (isset($_SESSION['last_entity']) && !empty($_SESSION['last_entity'])) {
                $entity = $_SESSION['last_entity'];
            }
        } else {
            return "To query my knowledge, please use the format: 'what is [entity]' or 'tell me about [entity]'.";
        }

        if ($entity === null) {
            return "To query my knowledge, please use the format: 'what is [entity]' or 'tell me about [entity]'.";
        }

        $knowledgeData = $this->knowledgeManager->getKnowledge($entity);
        $relationships = $this->knowledgeManager->getRelationships($entity);
        $inferredProperties = $this->knowledgeManager->getEntityProperties($entity);

        $response = "";

        if ($knowledgeData) {
            $response .= "'{$entity}' is ";
            $definitions = [];
            foreach ($knowledgeData['all_definitions'] as $def) {
                $source_users = implode(', ', array_unique($def['source_users']));
                $definitions[] = "'{$def['definition']}' (learned from {$source_users}, {$def['count']} time(s))";
            }
            $response .= implode(" or ", $definitions) . ".";
        } else {
            $response .= "I don't know about '{$entity}' yet.";
            // Add to deferred questions if no knowledge found
            $this->knowledgeManager->addDeferredQuestion($this->userId, $query_text, $entity);
        }

        if (!empty($relationships)) {
            $response .= "\nHere's what I know about its relationships:";
            foreach ($relationships as $rel) {
                $response .= "\n- It {$rel['relationship_type']} {$rel['related_entity']} (learned from {$rel['username']}).";
            }
        }

        if (!empty($inferredProperties)) {
            $response .= "\nHere are its properties (including inferred ones):";
            foreach ($inferredProperties as $prop) {
                $response .= "\n- It {$prop['relationship_type']} {$prop['property_name']} (learned from {$prop['username']}).";
            }
        }

        if (empty($knowledgeData) && empty($relationships) && empty($inferredProperties)) {
            $response .= " Would you like to teach me?";
        }

        return $response;
    }

    private function handleCodeGenerateIntent(string $message): string {
        // Example: "write code for a php function to add two numbers"
        $pattern = '/^(write code for|generate code for|create a function|php function|javascript function|python script|c function)\s*(.*?)$/i';
        if (preg_match($pattern, $message, $matches)) {
            $request = trim($matches[2]);
            $matched_phrase = strtolower(trim($matches[1]));

            $language = 'unknown';
            $functionality = $request;

            // Determine language based on matched phrase or full message
            if (str_contains($matched_phrase, 'php') || str_contains($message, 'php')) {
                $language = 'php';
            }
            elseif (str_contains($matched_phrase, 'javascript') || str_contains($matched_phrase, 'js') || str_contains($message, 'javascript') || str_contains($message, 'js')) {
                $language = 'javascript';
            }
            elseif (str_contains($matched_phrase, 'python') || str_contains($message, 'python')) {
                $language = 'python';
            }
            elseif (str_contains($matched_phrase, 'c function') || str_contains($message, 'c function') || str_contains($message, 'c code')) {
                $language = 'c';
            }

            // Refine functionality by removing language keywords if they are still present
            $functionality = str_ireplace(['php', 'javascript', 'js', 'python', 'c function', 'c code'], '', $functionality);
            $functionality = trim($functionality);

            return $this->taskEngine->generateCode($language, $functionality);
        }
        return "Please specify what kind of code you'd like me to generate, e.g., 'write code for a PHP function to add two numbers'.";
    }

    private function handleCreativeIntent(string $message): string {
        $type = 'unknown';

        if (str_contains($message, 'poem')) {
            $type = 'poem';
        }
        elseif (str_contains($message, 'story')) {
            $type = 'story';
        }
        elseif (str_contains($message, 'quote')) {
            $type = 'quote';
        }
        elseif (str_contains($message, 'metaphor')) {
            $type = 'metaphor';
        }

        return $this->taskEngine->generateCreativeContent($type);
    }

    private function handleSortIntent(string $message): string {
        $pattern = '/^(sort|order|arrange|list)\s+(.*?)$/i';
        if (preg_match($pattern, $message, $matches)) {
            $list = trim($matches[2]);
            return $this->taskEngine->sortList($list);
        }
        return "Please provide a list to sort, e.g., 'sort apples, bananas, cherries'.";
    }

    private function handleCalculateIntent(string $message): string {
        // Extract the part of the message after the trigger word (calculate, sum, average, etc.)
        $pattern = '/^(sum|average|count|total|mean|calculate(?:\s+sum|\s+average|\s+count)?)\s*(?:of\s+)?(.*?)$/i';
        if (preg_match($pattern, $message, $matches)) {
            $operation_trigger = strtolower(trim($matches[1])); // e.g., "sum", "average", "calculate sum"
            $data_string = trim($matches[2]); // e.g., "10,20,30" or "1,2,3,4,5"

            $operation = '';
            if (str_contains($operation_trigger, 'sum') || str_contains($operation_trigger, 'total')) {
                $operation = 'sum';
            }
            elseif (str_contains($operation_trigger, 'average') || str_contains($operation_trigger, 'mean')) {
                $operation = 'average';
            }
            elseif (str_contains($operation_trigger, 'count')) {
                $operation = 'count';
            }

            if (empty($operation)) {
                return "I couldn't determine the calculation operation. Please specify sum, average, or count.";
            }

            return $this->taskEngine->calculateData($data_string, $operation);
        }
        return "Please provide data to calculate, e.g., 'sum 1,2,3' or 'average 10,20,30'.";
    }

    private function handleTeachRelationshipIntent(string $message): string {
        // Example: "remember that apple is a type of fruit"
        // Example: "teach me that dog has property fur"
        $pattern = '/^(remember that|teach me that)\s+(.+?)\s+(is a type of|has property|is part of|is near)\s+(.+)$/i';
        if (preg_match($pattern, $message, $matches)) {
            $entity1 = rtrim(trim($matches[1]), '?');
            $relationshipType = trim($matches[2]);
            $entity2 = rtrim(trim($matches[3]), '?');

            if ($this->knowledgeManager->addRelationship($entity1, $relationshipType, $entity2, $this->userId)) {
                return "Okay, I've remembered that '{$entity1}' {$relationshipType} '{$entity2}'.";
            } else {
                return "I had trouble remembering that relationship. Please try again.";
            }
        }
        return "To teach me a relationship, please use the format: 'remember that [entity1] [is a type of/has property/is part of] [entity2]'.";
    }

    private function handleMathIntent(string $message): string {
        // Remove common question phrases and symbols that are not part of the expression
        $expression = str_ireplace(['what is', 'calculate', 'eval', 'compute', 'result of', '?', '='], '', $message);
        $expression = trim($expression);

        if (empty($expression)) {
            return "Please provide a mathematical expression to solve, e.g., '7+9-6/3' or '6%3+5'.";
        }

        return $this->taskEngine->solveMathProblem($expression);
    }
}

?>