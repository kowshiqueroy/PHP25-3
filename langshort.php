<?php
// lang.php — EduBox PHP Code Runner

// 1. Default EduBox program and scan input
$defaultCode = <<<'CODE'
box a = 2;
box b = 3.0;
a = a + 1;
box sum = a + b;
output("sum = " + sum);

if (sum - 6) {
  output("sum is not 6");
} else {
  output("sum is exactly 6");
}

box ch = 'Z';
loop(3) {
  output(ch);
}

box count = 1;
loop(4) {
  output("count = " + count);
  count = count + 1;
}

input(userToken);
output("you entered: " + userToken);
CODE;

$defaultScan = "Hello123";

// 2. Tokenizer
function tokenize(string $input): array {
    $pattern = '/\s*('
             . '==|'                       // two-char op
             . '[\{\}\(\);=+\-\*\/]|'     // single chars
             . '\bbox\b|\bif\b|\belse\b|\bloop\b|\boutput\b|\binput\b|' 
             . '"[^"]*"|\'[^\']*\'|'       // string literals
             . '\d+(?:\.\d+)?|'            // numbers
             . '[A-Za-z_]\w*'              // identifiers
             . ')/';
    preg_match_all($pattern, $input, $m);
    $raws = $m[1];
    $tokens = [];
    foreach ($raws as $r) {
        if (in_array($r, ['box','if','else','loop','output','input'], true)) {
            $tokens[] = ['type'=>'KEYWORD', 'value'=>$r];
        }
        elseif (preg_match('/^\d+(\.\d+)?$/', $r)) {
            $tokens[] = ['type'=>'NUMBER',  'value'=>$r];
        }
        elseif (preg_match('/^".*"$/', $r) || preg_match("/^'.*'$/", $r)) {
            // strip quotes
            $tokens[] = ['type'=>'STRING', 'value'=>substr($r,1,-1)];
        }
        elseif (preg_match('/^[A-Za-z_]\w*$/', $r)) {
            $tokens[] = ['type'=>'IDENT',  'value'=>$r];
        }
        else {
            // anything else: a symbol like ( ) + - * / ; { }
            $tokens[] = ['type'=>'SYMBOL', 'value'=>$r];
        }
    }
    // EOF sentinel
    $tokens[] = ['type'=>'EOF','value'=>null];
    return $tokens;
}

// 3. Parser
class Parser {
    private array $tokens;
    private int   $pos = 0;

    public function __construct(array $tokens) {
        $this->tokens = $tokens;
    }

    private function peek(): array {
        return $this->tokens[$this->pos];
    }
    private function next(): array {
        return $this->tokens[$this->pos++];
    }
    // eat a specific (type, optional value)
    private function eat(string $type, string $value = null): array {
        $tk = $this->peek();
        if ($tk['type'] === $type && ($value===null || $tk['value'] === $value)) {
            return $this->next();
        }
        $got = $tk['value'] ?? $tk['type'];
        $expected = $value ?? $type;
        throw new Exception("Expected “{$expected}” but got “{$got}”");
    }

    // <program> ::= { <statement> }
    public function parseProgram(): array {
        $stmts = [];
        while ($this->peek()['type'] !== 'EOF') {
            $stmts[] = $this->parseStatement();
        }
        return $stmts;
    }

    // <statement> ::= <varDecl> ";" | <assignment> ";" | <ifStmt> | <loopStmt> | <ioStmt> ";"
    private function parseStatement(): array {
        $tk = $this->peek();
        if ($tk['type']==='KEYWORD' && $tk['value']==='box') {
            $stmt = $this->parseVarDecl();
            $this->eat('SYMBOL',';');
            return $stmt;
        }
        if ($tk['type']==='IDENT') {
            $stmt = $this->parseAssignment();
            $this->eat('SYMBOL',';');
            return $stmt;
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='if') {
            return $this->parseIf();
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='loop') {
            return $this->parseLoop();
        }
        if ($tk['type']==='KEYWORD' && in_array($tk['value'], ['output','input'], true)) {
            $stmt = $this->parseIo();
            $this->eat('SYMBOL',';');
            return $stmt;
        }
        throw new Exception("Unknown statement start: “{$tk['value']}”");
    }

    // <varDecl> ::= "box" <ident> "=" <expr>
    private function parseVarDecl(): array {
        $this->eat('KEYWORD','box');
        $id = $this->eat('IDENT');
        $name = $id['value'];
        $this->eat('SYMBOL','=');
        $expr = $this->parseExpr();
        return ['type'=>'varDecl','name'=>$name,'expr'=>$expr];
    }

    // <assignment> ::= <ident> "=" <expr>
    private function parseAssignment(): array {
        $id = $this->eat('IDENT');
        $name = $id['value'];
        $this->eat('SYMBOL','=');
        $expr = $this->parseExpr();
        return ['type'=>'assign','name'=>$name,'expr'=>$expr];
    }

    // <ifStmt> ::= "if" "(" <expr> ")" "{" { <statement> } "}" [ "else" "{" { <statement> } "}" ]
    private function parseIf(): array {
        $this->eat('KEYWORD','if');
        $this->eat('SYMBOL','(');
          $cond = $this->parseExpr();
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $then = [];
          while ($this->peek()['value'] !== '}') {
            $then[] = $this->parseStatement();
          }
        $this->eat('SYMBOL','}');
        $els = null;
        if ($this->peek()['type']==='KEYWORD' && $this->peek()['value']==='else') {
          $this->eat('KEYWORD','else');
          $this->eat('SYMBOL','{');
            $els = [];
            while ($this->peek()['value'] !== '}') {
              $els[] = $this->parseStatement();
            }
          $this->eat('SYMBOL','}');
        }
        return ['type'=>'if','cond'=>$cond,'then'=>$then,'else'=>$els];
    }

    // <loopStmt> ::= "loop" "(" <expr> ")" "{" { <statement> } "}"
    private function parseLoop(): array {
        $this->eat('KEYWORD','loop');
        $this->eat('SYMBOL','(');
          $cnt = $this->parseExpr();
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $body = [];
          while ($this->peek()['value'] !== '}') {
            $body[] = $this->parseStatement();
          }
        $this->eat('SYMBOL','}');
        return ['type'=>'loop','count'=>$cnt,'body'=>$body];
    }

    // <ioStmt> ::= "output" "(" <expr> ")" | "input" "(" <ident> ")"
    private function parseIo(): array {
        $tk = $this->next();
        if ($tk['value']==='output') {
            $this->eat('SYMBOL','(');
            $expr = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return ['type'=>'output','expr'=>$expr];
        }
        // else must be input
        $this->eat('SYMBOL','(');
        $id = $this->eat('IDENT');
        $this->eat('SYMBOL',')');
        return ['type'=>'input','name'=>$id['value']];
    }

    // Expression parsing with correct precedence
    // <expr> ::= <term> { ("+" | "-") <term> }
    // <term> ::= <factor> { ("*" | "/") <factor> }
    // <factor> ::= <number> | <string> | <ident> | "(" <expr> ")"

    private function parseExpr(): array {
        $node = $this->parseTerm();
        while ($this->peek()['type']==='SYMBOL'
            && in_array($this->peek()['value'], ['+','-'], true)
        ) {
            $op   = $this->next()['value'];
            $right= $this->parseTerm();
            $node = ['type'=>'binop','op'=>$op,'left'=>$node,'right'=>$right];
        }
        return $node;
    }

    private function parseTerm(): array {
        $node = $this->parseFactor();
        while ($this->peek()['type']==='SYMBOL'
            && in_array($this->peek()['value'], ['*','/'], true)
        ) {
            $op    = $this->next()['value'];
            $right = $this->parseFactor();
            $node  = ['type'=>'binop','op'=>$op,'left'=>$node,'right'=>$right];
        }
        return $node;
    }

    private function parseFactor(): array {
        $tk = $this->peek();
        if ($tk['type']==='SYMBOL' && $tk['value']==='(') {
            $this->eat('SYMBOL','(');
            $inner = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return $inner;
        }
        if ($tk['type']==='NUMBER') {
            $n = $this->next()['value'];
            return ['type'=>'number','value'=>floatval($n)];
        }
        if ($tk['type']==='STRING') {
            $s = $this->next()['value'];
            return ['type'=>'string','value'=>$s];
        }
        if ($tk['type']==='IDENT') {
            $i = $this->next()['value'];
            return ['type'=>'ident','name'=>$i];
        }
        throw new Exception("Unexpected factor “{$tk['value']}”");
    }
}

// 4. Interpreter
function runProgram(array $ast, array $inputs): array {
    $env     = [];
    $out     = [];
    $scanIdx = 0;

    // evaluate an expression node
    $evalExpr = null;
    $evalExpr = function($node) use (&$evalExpr, &$env) {
        switch ($node['type']) {
            case 'number': return $node['value'];
            case 'string': return $node['value'];
            case 'ident':  return $env[$node['name']] ?? null;
            case 'binop':
                $L = $evalExpr($node['left']);
                $R = $evalExpr($node['right']);
                switch ($node['op']) {
                    case '+':
                        // string concat if either is string
                        return (is_string($L)||is_string($R)) ? ($L . $R) : ($L + $R);
                    case '-': return $L - $R;
                    case '*': return $L * $R;
                    case '/': return $L / $R;
                }
        }
        return null;
    };

    // execute a statement
    $exec = function($stmt) use (&$exec, &$evalExpr, &$env, &$out, &$inputs, &$scanIdx) {
        switch ($stmt['type']) {
            case 'varDecl':
                $env[$stmt['name']] = $evalExpr($stmt['expr']);
                break;
            case 'assign':
                $env[$stmt['name']] = $evalExpr($stmt['expr']);
                break;
            case 'output':
                $out[] = $evalExpr($stmt['expr']);
                break;
            case 'input':
                $tok = $inputs[$scanIdx++] ?? '';
                $env[$stmt['name']] = is_numeric($tok) ? +$tok : $tok;
                break;
            case 'if':
                if ($evalExpr($stmt['cond'])) {
                    foreach ($stmt['then'] as $_s) $exec($_s);
                } elseif ($stmt['else']) {
                    foreach ($stmt['else'] as $_s) $exec($_s);
                }
                break;
            case 'loop':
                $n = $evalExpr($stmt['count']);
                for ($i = 0; $i < $n; $i++) {
                    foreach ($stmt['body'] as $_s) $exec($_s);
                }
                break;
        }
    };

    foreach ($ast as $s) {
        $exec($s);
    }
    return $out;
}

// 5. Handle form submission
$error  = '';
$result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code   = $_POST['code'] ?? '';
    $scan   = trim($_POST['scan'] ?? '');
    $inputs = $scan === '' ? [] : preg_split('/\s+/', $scan);

    try {
        $tokens = tokenize($code);
        $parser = new Parser($tokens);
        $ast    = $parser->parseProgram();
        $result = runProgram($ast, $inputs);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduBox PHP Code Runner</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <style>
    body    { background: #f8f9fa; }
    textarea { font-family: monospace; }
    pre      { background: #fff; }
  </style>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4">EduBox Code Runner</h1>

    <form method="POST">
      <div class="mb-3">
        <label for="codeArea" class="form-label">EduBox Program</label>
        <textarea
          id="codeArea"
          name="code"
          class="form-control"
          rows="14"
        ><?php echo htmlspecialchars($_POST['code'] ?? $defaultCode); ?></textarea>
      </div>

      <div class="mb-3">
        <label for="scanInput" class="form-label">
          Scan Input (space-separated)
        </label>
        <input
          id="scanInput"
          name="scan"
          class="form-control"
          value="<?php echo htmlspecialchars($_POST['scan'] ?? $defaultScan); ?>"
        >
      </div>

      <button type="submit" class="btn btn-primary">Run</button>
    </form>

    <div class="mt-4">
      <h4>Output</h4>
      <pre class="p-3 border" style="min-height:150px;">
<?php
if ($error) {
    echo "Error: " . htmlspecialchars($error);
} else {
    echo htmlspecialchars(implode("\n", $result));
}
?>
      </pre>
    </div>
  </div>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>