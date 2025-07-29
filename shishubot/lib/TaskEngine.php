<?php

class TaskEngine {
    public function generateCode(string $language, string $functionality): string {
        switch (strtolower($language)) {
            case 'php':
                if (str_contains(strtolower($functionality), 'add two numbers')) {
                    return "```php\nfunction addNumbers($num1, $num2) {\n    return $num1 + $num2;\n}\n```";
                } elseif (str_contains(strtolower($functionality), 'hello world')) {
                    return "```php\n<?php\necho \"Hello, World!\";\n?>\n```";
                }
                break;
            case 'javascript':
            case 'js':
                if (str_contains(strtolower($functionality), 'add two numbers')) {
                    return "```javascript\nfunction addNumbers(num1, num2) {\n    return num1 + num2;\n}\n```";
                } elseif (str_contains(strtolower($functionality), 'hello world')) {
                    return "```javascript\nconsole.log(\"Hello, World!\");\n```";
                }
                break;
            case 'python':
                if (str_contains(strtolower($functionality), 'add two numbers')) {
                    return "```python\ndef add_numbers(num1, num2):\n    return num1 + num2\n```";
                } elseif (str_contains(strtolower($functionality), 'hello world')) {
                    return "```python\nprint(\"Hello, World!\")\n```";
                }
                break;
            case 'c':
                if (str_contains(strtolower($functionality), 'hello world')) {
                    return "```c\n#include <stdio.h>\n\nint main() {\n    printf(\"Hello, World!\\n\");\n    return 0;\n}\n```";
                } elseif (str_contains(strtolower($functionality), 'add two numbers')) {
                    return "```c\nint addNumbers(int num1, int num2) {\n    return num1 + num2;\n}\n```";
                }
                break;
        }
        return "I can generate a basic {$language} snippet for '{$functionality}'. (More complex code generation is not yet implemented)";
    }

    public function reviewCode(string $codeSnippet): string {
        // Placeholder for code review logic
        return "I can review your code. (Not yet implemented)";
    }

    public function sortList(string $list): string {
        $items = array_map('trim', explode(',', $list));
        sort($items);
        return "Here's your sorted list: " . implode(', ', $items) . ".";
    }

    public function calculateData(string $data, string $operation): string {
        // Remove any non-numeric characters except commas and periods for decimals
        $cleaned_data = preg_replace('/[^0-9.,]/', '', $data);
        $numbers = array_map('trim', explode(',', $cleaned_data));
        $numbers = array_filter($numbers, 'is_numeric'); // Ensure only numbers are processed

        if (empty($numbers)) {
            return "I couldn't find any valid numbers to perform the calculation on.";
        }

        switch (strtolower($operation)) {
            case 'sum':
            case 'total':
                return "The sum is: " . array_sum($numbers) . ".";
            case 'average':
            case 'mean':
                return "The average is: " . (array_sum($numbers) / count($numbers)) . ".";
            case 'count':
                return "The count is: " . count($numbers) . ".";
            default:
                return "I can perform sum, average, and count operations. Please specify one.";
        }
    }

    public function filterDataset(string $dataset, string $criteria): string {
        // Placeholder for dataset filtering logic
        return "I can filter datasets. (Not yet implemented)";
    }

    public function solveMathProblem(string $problem): string {
        // Basic validation to prevent arbitrary code execution via eval()
        if (!preg_match('/^[0-9+\-*\/%().\s]+$/', $problem)) {
            return "Invalid mathematical expression. Please use only numbers and basic operators (+, -, *, /, %, .).";
        }

        try {
            // Suppress errors from eval() and catch them
            set_error_handler(function($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });
            $result = eval('return ' . $problem . ';');
            restore_error_handler();

            if ($result === null) {
                return "I couldn't solve that expression. Please check its format.";
            }
            return "The result is: " . $result . ".";
        } catch (ErrorException $e) {
            return "Error solving expression: " . $e->getMessage();
        } catch (ParseError $e) {
            return "Syntax error in expression: " . $e->getMessage();
        } catch (Throwable $e) {
            return "An unexpected error occurred while solving: " . $e->getMessage();
        }
    }

    public function generateCreativeContent(string $type): string {
        switch (strtolower($type)) {
            case 'poem':
                return "Here's a short poem:\n\nIn realms of code, where logic gleams,\nA bot awakes, fulfilling dreams.\nWith data fed, and lessons learned,\nA new intelligence, brightly burned.";
            case 'story':
                return "Once upon a time, in a digital land, lived a curious bot named shishuBot. It yearned to learn and grow, interacting with humans to expand its knowledge. Every new fact was a treasure, every conversation a step towards true understanding.";
            case 'quote':
                $quotes = [
                    "The only way to do great work is to love what you do. - Steve Jobs",
                    "Innovation distinguishes between a leader and a follower. - Steve Jobs",
                    "The future belongs to those who believe in the beauty of their dreams. - Eleanor Roosevelt",
                    "Tell me and I forget. Teach me and I remember. Involve me and I learn. - Benjamin Franklin"
                ];
                return "Motivational Quote: " . $quotes[array_rand($quotes)];
            case 'metaphor':
                return "Life is like a complex algorithm, constantly processing inputs, making decisions, and striving for an optimal output, even when the variables are unpredictable.";
            default:
                return "I can generate creative content. What kind would you like? (poem, story, quote, metaphor)";
        }
    }
}

?>