<?php

include_once("header.php");

// 1. Default program with more examples
$defaultCode = <<<'CODE'
// Variable declarations and arithmetic
box a = 10;
box b = 3;
box name = "BanglaScript";

// Basic arithmetic operations
box sum = a + b;
box diff = a - b;
box prod = a * b;
box quot = a / b;
box mod = a % b;

output("=== Arithmetic Results ===");
output("Sum: " + sum);
output("Difference: " + diff);
output("Product: " + prod);
output("Quotient: " + quot);
output("Modulo: " + mod);

// Comparison operations
box isEqual = (a == b);
box isNotEqual = (a != b);
box isGreater = (a > b);
box isLess = (a < b);

output("=== Comparison Results ===");
output("a == b: " + isEqual);
output("a != b: " + isNotEqual);
output("a > b: " + isGreater);
output("a < b: " + isLess);

// Conditional statement
if (a > b) {
    output("\n" + a + " is greater than " + b);
} else {
    output("\n" + a + " is not greater than " + b);
}

// Fixed count loop
output("=== Fixed Count Loop ===");
box counter = 0;
loop(3) {
    output("Iteration: " + counter);
    counter = counter + 1;
}

// While-style loop
output("=== While Loop ===");
box x = 0;
loop(x < 3) {
    output("x = " + x);
    x = x + 1;
}

// Multiple conditions loop
output("=== Multiple Conditions Loop ===");
box y = 0;
loop(y < 2, sum > 5) {
    output("y = " + y + ", sum = " + sum);
    y = y + 1;
}

// Input demonstration
output("=== Input Demo ===");
output("Enter your name:");
input(userName);
output("Hello, " + userName + "!");

// String operations
box greeting = "Welcome to " + name;
output("" + greeting);
CODE;

$defaultScan = "Kowshique";

// 2. Enhanced Tokenizer with better error handling
function tokenize(string $input): array {
    if (empty(trim($input))) {
        throw new Exception("Empty input provided");
    }
    
    // Remove comments
    $input = preg_replace('!//.*!', '', $input);
    $input = preg_replace('!/\*.*?\*/!s', '', $input);
    
    // Enhanced pattern with better string handling
    $pattern = '/\s*('
             . '==|!=|<>|<=|>=|!<|!>|&&|\|\||\band\b|\bor\b|'   // comparison & logical ops
             . '[+\-*\/%<>!]|'                                  // arithmetic & comparison
             . '[=]|'                                           // assignment
             . '[\{\}\(\);,]|'                                  // punctuation
             . '\bbox\b|\bif\b|\belse\b|\bloop\b|\boutput\b|\binput\b|' // keywords
             . '"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'|' // improved string literals
             . '\d+(?:\.\d+)?|'                                  // numbers (int/float)
             . '[A-Za-z_][A-Za-z0-9_]*'                         // identifiers
             . ')/';

    preg_match_all($pattern, $input, $matches);
    $rawTokens = $matches[1] ?? [];
    
    if (empty($rawTokens)) {
        throw new Exception("No valid tokens found in input");
    }
    
    $tokens = [];
    $lineNumber = 1;
    
    foreach ($rawTokens as $raw) {
        $token = ['value' => $raw, 'line' => $lineNumber];
        
        if (in_array($raw, ['box', 'if', 'else', 'loop', 'output', 'input'], true)) {
            $token['type'] = 'KEYWORD';
        }
        elseif (in_array($raw, ['==', '!=', '<>', '<=', '>=', '!<', '!>', '&&', '||', 'and', 'or', '!'], true)) {
            $token['type'] = 'OP';
        }
        elseif (in_array($raw, ['+', '-', '*', '/', '%', '<', '>'], true)) {
            $token['type'] = 'OP';
        }
        elseif ($raw === '=') {
            $token['type'] = 'ASSIGN';
        }
        elseif (in_array($raw, ['{', '}', '(', ')', ';', ','], true)) {
            $token['type'] = 'SYMBOL';
        }
        elseif (preg_match('/^\d+(?:\.\d+)?$/', $raw)) {
            $token['type'] = 'NUMBER';
            $token['value'] = is_float($raw + 0) ? floatval($raw) : intval($raw);
        }
        elseif (preg_match('/^"(?:[^"\\\\]|\\\\.)*"$|^\'(?:[^\'\\\\]|\\\\.)*\'$/', $raw)) {
            $token['type'] = 'STRING';
            $token['value'] = stripslashes(substr($raw, 1, -1));
        }
        elseif (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $raw)) {
            $token['type'] = 'IDENT';
        }
        else {
            throw new Exception("Invalid token '{$raw}' at line {$lineNumber}");
        }
        
        $tokens[] = $token;
    }
    
    $tokens[] = ['type' => 'EOF', 'value' => null, 'line' => $lineNumber];
    return $tokens;
}

// 3. Enhanced Parser with better error reporting
class Parser {
    private array $tokens;
    private int $position = 0;
    private int $maxPosition = 0;
    
    public function __construct(array $tokens) {
        $this->tokens = $tokens;
        $this->maxPosition = count($tokens) - 1;
    }
    
    private function current(): array {
        return $this->tokens[$this->position] ?? ['type' => 'EOF', 'value' => null, 'line' => 0];
    }
    
    private function advance(): array {
        if ($this->position < $this->maxPosition) {
            $this->position++;
        }
        return $this->current();
    }
    
    private function consume(string $expectedType, ?string $expectedValue = null): array {
        $token = $this->current();
        
        if ($token['type'] !== $expectedType) {
            throw new Exception("Expected {$expectedType} but got {$token['type']} at line {$token['line']}");
        }
        
        if ($expectedValue !== null && $token['value'] !== $expectedValue) {
            throw new Exception("Expected '{$expectedValue}' but got '{$token['value']}' at line {$token['line']}");
        }
        
        $this->advance();
        return $token;
    }
    
    public function parseProgram(): array {
        $statements = [];
        
        while ($this->current()['type'] !== 'EOF') {
            $stmt = $this->parseStatement();
            if ($stmt !== null) {
                $statements[] = $stmt;
            }
        }
        
        return $statements;
    }
    
    private function parseStatement(): ?array {
        $token = $this->current();
        
        switch ($token['type']) {
            case 'KEYWORD':
                switch ($token['value']) {
                    case 'box':
                        return $this->parseVariableDeclaration();
                    case 'if':
                        return $this->parseIfStatement();
                    case 'loop':
                        return $this->parseLoopStatement();
                    case 'output':
                        return $this->parseOutputStatement();
                    case 'input':
                        return $this->parseInputStatement();
                    default:
                        throw new Exception("Unknown keyword '{$token['value']}' at line {$token['line']}");
                }
            case 'IDENT':
                return $this->parseAssignment();
            default:
                throw new Exception("Unexpected token '{$token['value']}' at line {$token['line']}");
        }
    }
    
    private function parseVariableDeclaration(): array {
        $this->consume('KEYWORD', 'box');
        $name = $this->consume('IDENT');
        $this->consume('ASSIGN', '=');
        $expression = $this->parseExpression();
        $this->consume('SYMBOL', ';');
        
        return [
            'type' => 'varDecl',
            'name' => $name['value'],
            'expression' => $expression
        ];
    }
    
    private function parseAssignment(): array {
        $name = $this->consume('IDENT');
        $this->consume('ASSIGN', '=');
        $expression = $this->parseExpression();
        $this->consume('SYMBOL', ';');
        
        return [
            'type' => 'assign',
            'name' => $name['value'],
            'expression' => $expression
        ];
    }
    
    private function parseIfStatement(): array {
        $this->consume('KEYWORD', 'if');
        $this->consume('SYMBOL', '(');
        $condition = $this->parseExpression();
        $this->consume('SYMBOL', ')');
        $this->consume('SYMBOL', '{');
        
        $thenBlock = [];
        while ($this->current()['value'] !== '}') {
            $stmt = $this->parseStatement();
            if ($stmt !== null) {
                $thenBlock[] = $stmt;
            }
        }
        $this->consume('SYMBOL', '}');
        
        $elseBlock = null;
        if ($this->current()['type'] === 'KEYWORD' && $this->current()['value'] === 'else') {
            $this->consume('KEYWORD', 'else');
            $this->consume('SYMBOL', '{');
            $elseBlock = [];
            while ($this->current()['value'] !== '}') {
                $stmt = $this->parseStatement();
                if ($stmt !== null) {
                    $elseBlock[] = $stmt;
                }
            }
            $this->consume('SYMBOL', '}');
        }
        
        return [
            'type' => 'if',
            'condition' => $condition,
            'thenBlock' => $thenBlock,
            'elseBlock' => $elseBlock
        ];
    }
    
    private function parseLoopStatement(): array {
        $this->consume('KEYWORD', 'loop');
        $this->consume('SYMBOL', '(');
        
        $conditions = [$this->parseExpression()];
        while ($this->current()['value'] === ',') {
            $this->consume('SYMBOL', ',');
            $conditions[] = $this->parseExpression();
        }
        
        $this->consume('SYMBOL', ')');
        $this->consume('SYMBOL', '{');
        
        $body = [];
        while ($this->current()['value'] !== '}') {
            $stmt = $this->parseStatement();
            if ($stmt !== null) {
                $body[] = $stmt;
            }
        }
        $this->consume('SYMBOL', '}');
        
        return [
            'type' => 'loop',
            'conditions' => $conditions,
            'body' => $body
        ];
    }
    
    private function parseOutputStatement(): array {
        $this->consume('KEYWORD', 'output');
        $this->consume('SYMBOL', '(');
        $expression = $this->parseExpression();
        $this->consume('SYMBOL', ')');
        $this->consume('SYMBOL', ';');
        
        return [
            'type' => 'output',
            'expression' => $expression
        ];
    }
    
    private function parseInputStatement(): array {
        $this->consume('KEYWORD', 'input');
        $this->consume('SYMBOL', '(');
        $variable = $this->consume('IDENT');
        $this->consume('SYMBOL', ')');
        $this->consume('SYMBOL', ';');
        
        return [
            'type' => 'input',
            'variable' => $variable['value']
        ];
    }
    
    // Expression parsing with proper precedence
    private function parseExpression(): array {
        return $this->parseLogicalOr();
    }
    
    private function parseLogicalOr(): array {
        $left = $this->parseLogicalAnd();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['||', 'or'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseLogicalAnd();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseLogicalAnd(): array {
        $left = $this->parseEquality();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['&&', 'and'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseEquality();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseEquality(): array {
        $left = $this->parseComparison();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['==', '!=', '<>'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseComparison();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseComparison(): array {
        $left = $this->parseAddition();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['<', '>', '<=', '>=', '!<', '!>'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseAddition();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseAddition(): array {
        $left = $this->parseMultiplication();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['+', '-'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseMultiplication();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseMultiplication(): array {
        $left = $this->parseUnary();
        
        while ($this->current()['type'] === 'OP' && 
               in_array($this->current()['value'], ['*', '/', '%'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $right = $this->parseUnary();
            $left = [
                'type' => 'binary',
                'operator' => $operator,
                'left' => $left,
                'right' => $right
            ];
        }
        
        return $left;
    }
    
    private function parseUnary(): array {
        if ($this->current()['type'] === 'OP' && 
            in_array($this->current()['value'], ['!', '-'], true)) {
            $operator = $this->current()['value'];
            $this->advance();
            $operand = $this->parseUnary();
            return [
                'type' => 'unary',
                'operator' => $operator,
                'operand' => $operand
            ];
        }
        
        return $this->parsePrimary();
    }
    
    private function parsePrimary(): array {
        $token = $this->current();
        
        switch ($token['type']) {
            case 'NUMBER':
                $this->advance();
                return ['type' => 'number', 'value' => $token['value']];
                
            case 'STRING':
                $this->advance();
                return ['type' => 'string', 'value' => $token['value']];
                
            case 'IDENT':
                $this->advance();
                return ['type' => 'identifier', 'name' => $token['value']];
                
            case 'SYMBOL':
                if ($token['value'] === '(') {
                    $this->advance();
                    $expr = $this->parseExpression();
                    $this->consume('SYMBOL', ')');
                    return $expr;
                }
                // fallthrough
                
            default:
                throw new Exception("Unexpected token '{$token['value']}' at line {$token['line']}");
        }
    }
}

// 4. Enhanced Interpreter with better error handling
class Interpreter {
    private array $variables = [];
    private array $outputs = [];
    private array $inputs = [];
    private int $inputIndex = 0;
    
    public function __construct(array $inputs = []) {
        $this->inputs = $inputs;
    }
    
    public function execute(array $ast): array {
        $this->variables = [];
        $this->outputs = [];
        $this->inputIndex = 0;
        
        foreach ($ast as $statement) {
            $this->executeStatement($statement);
        }
        
        return $this->outputs;
    }
    
    private function executeStatement(array $stmt): void {
        switch ($stmt['type']) {
            case 'varDecl':
                $value = $this->evaluateExpression($stmt['expression']);
                $this->variables[$stmt['name']] = $value;
                break;
                
            case 'assign':
                $value = $this->evaluateExpression($stmt['expression']);
                $this->variables[$stmt['name']] = $value;
                break;
                
            case 'output':
                $value = $this->evaluateExpression($stmt['expression']);
                $this->outputs[] = $this->toString($value);
                break;
                
            case 'input':
                $input = $this->inputs[$this->inputIndex++] ?? '';
                $this->variables[$stmt['variable']] = is_numeric($input) ? +$input : $input;
                break;
                
            case 'if':
                $condition = $this->evaluateExpression($stmt['condition']);
                if ($this->isTruthy($condition)) {
                    foreach ($stmt['thenBlock'] as $s) {
                        $this->executeStatement($s);
                    }
                } elseif ($stmt['elseBlock']) {
                    foreach ($stmt['elseBlock'] as $s) {
                        $this->executeStatement($s);
                    }
                }
                break;
                
            case 'loop':
                $this->executeLoop($stmt);
                break;
                
            default:
                throw new Exception("Unknown statement type: {$stmt['type']}");
        }
    }
    
    private function executeLoop(array $stmt): void {
        $conditions = $stmt['conditions'];
        
        // Check if it's a fixed-count loop (single number condition)
        if (count($conditions) === 1 && $conditions[0]['type'] === 'number') {
            $count = intval($this->evaluateExpression($conditions[0]));
            for ($i = 0; $i < $count; $i++) {
                foreach ($stmt['body'] as $s) {
                    $this->executeStatement($s);
                }
            }
        } else {
            // While-style loop with multiple conditions
            $maxIterations = 10000; // Prevent infinite loops
            $iterations = 0;
            
            while ($iterations < $maxIterations) {
                $allConditionsTrue = true;
                foreach ($conditions as $condition) {
                    if (!$this->isTruthy($this->evaluateExpression($condition))) {
                        $allConditionsTrue = false;
                        break;
                    }
                }
                
                if (!$allConditionsTrue) {
                    break;
                }
                
                foreach ($stmt['body'] as $s) {
                    $this->executeStatement($s);
                }
                
                $iterations++;
            }
            
            if ($iterations >= $maxIterations) {
                throw new Exception("Loop exceeded maximum iterations limit");
            }
        }
    }
    
    private function evaluateExpression(array $expr) {
        switch ($expr['type']) {
            case 'number':
                return $expr['value'];
                
            case 'string':
                return $expr['value'];
                
            case 'identifier':
                if (!isset($this->variables[$expr['name']])) {
                    throw new Exception("Undefined variable: {$expr['name']}");
                }
                return $this->variables[$expr['name']];
                
            case 'binary':
                return $this->evaluateBinaryOperation($expr);
                
            case 'unary':
                return $this->evaluateUnaryOperation($expr);
                
            default:
                throw new Exception("Unknown expression type: {$expr['type']}");
        }
    }
    
    private function evaluateBinaryOperation(array $expr) {
        $left = $this->evaluateExpression($expr['left']);
        $right = $this->evaluateExpression($expr['right']);
        
        switch ($expr['operator']) {
            case '+':
                return (is_string($left) || is_string($right)) ? 
                       $this->toString($left) . $this->toString($right) : 
                       $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '/':
                if ($right == 0) {
                    throw new Exception("Division by zero");
                }
                return $left / $right;
            case '%':
                if ($right == 0) {
                    throw new Exception("Modulo by zero");
                }
                return $left % $right;
            case '==':
                return $left == $right;
            case '!=':
            case '<>':
                return $left != $right;
            case '<':
                return $left < $right;
            case '>':
                return $left > $right;
            case '<=':
                return $left <= $right;
            case '>=':
                return $left >= $right;
            case '!<':
                return $left >= $right;
            case '!>':
                return $left <= $right;
            case '&&':
            case 'and':
                return $this->isTruthy($left) && $this->isTruthy($right);
            case '||':
            case 'or':
                return $this->isTruthy($left) || $this->isTruthy($right);
            default:
                throw new Exception("Unknown binary operator: {$expr['operator']}");
        }
    }
    
    private function evaluateUnaryOperation(array $expr) {
        $operand = $this->evaluateExpression($expr['operand']);
        
        switch ($expr['operator']) {
            case '!':
                return !$this->isTruthy($operand);
            case '-':
                return -$operand;
            default:
                throw new Exception("Unknown unary operator: {$expr['operator']}");
        }
    }
    
    private function isTruthy($value): bool {
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return $value != 0;
        if (is_string($value)) return $value !== '';
        return false;
    }
    
    private function toString($value): string {
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_numeric($value)) return strval($value);
        return strval($value);
    }
}

// 5. Main execution logic
function runBanglaScript(string $code, array $inputs = []): array {
    try {
        $tokens = tokenize($code);
        $parser = new Parser($tokens);
        $ast = $parser->parseProgram();
        $interpreter = new Interpreter($inputs);
        return $interpreter->execute($ast);
    } catch (Exception $e) {
        throw new Exception("BanglaScript Error: " . $e->getMessage());
    }
}

// 6. Handle form submission
$error = '';
$output = [];
$executionTime = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $scanInput = trim($_POST['scan'] ?? '');
    $inputs = empty($scanInput) ? [] : preg_split('/\s+/', $scanInput);
    
    $startTime = microtime(true);
    
    try {
        $output = runBanglaScript($code, $inputs);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    } catch (Exception $e) {
        $error = $e->getMessage();
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bangla Script Code Runner</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #121212;
      color: #e0e0e0;
      font-family: 'Roboto', sans-serif;
    }
    .container {
      max-width: 90%;
      margin: auto;
    }
    h1 {
      color: #76ff03;
      text-shadow: 0 0 5px #76ff03, 0 0 10px #76ff03;
    }
    textarea, input {
      background: #1e1e1e;
      color: #e0e0e0;
      border: 1px solid #333;
    }
    .btn-primary {
      background-color: #76ff03;
      border-color: #76ff03;
    }
    .btn-primary:hover {
      background-color: #64dd17;
      border-color: #64dd17;
    }
    .output {
      background-color: #1e1e1e;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
    }
    pre {
      background: none;
      color: #76ff03;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4 text-center">Bangla Script</h1>

    <div class="mb-4 text-center">
      <a href="bnf/" class="btn btn-secondary me-2">BNF</a>
      <a href="example/" class="btn btn-secondary me-2">Example</a>
      <a href="lesson/" class="btn btn-secondary me-2">Lesson</a>
      <a href="contact/" class="btn btn-secondary">Contact</a>
    </div>

    <div class="d-flex justify-content-around shadow p-3 mb-5 bg-body-tertiary rounded">
        <p style="color: red;">সহজ কোডে প্রোগ্রামিং শিখুন।</p>
        <p style="color: red;">বাংলা সিনট্যাক্স শীঘ্রই আসছে।</p>
    </div>
        <p class="text-center">
            Total Visitors: <?php echo $total_ip; ?>, Total Hits: <?php echo $total_hits; ?> in this page
 
            <a href="https://wa.me/+8801632950179?text=About%20BanglaScript: " target="_blank">Feedback on WhatsApp</a>
        </p>
    <div class="row">
      <div class="col-sm-6">
        <form method="POST" class="mb-4">
          <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <textarea id="code" name="code" class="form-control" style="height: 300px;"><?php
              echo htmlspecialchars($_POST['code'] ?? $defaultCode);
            ?></textarea>
          </div>
        
      </div>
      <div class="col-sm-6">

        <div class="mb-3">
            <label for="scan" class="form-label">Scan Input</label>
            <input id="scan" name="scan" class="form-control"
                   value="<?php echo htmlspecialchars($_POST['scan'] ?? $defaultScan) ?>">
          </div>
          <button type="submit" class="btn btn-primary w-100">Run</button>
        </form>
        <div class="output">
          <h4>Output</h4>
          <pre><?php
            if ($error) echo "Error: " . htmlspecialchars($error);
            else echo htmlspecialchars(implode("\n", $output));
          ?></pre>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

