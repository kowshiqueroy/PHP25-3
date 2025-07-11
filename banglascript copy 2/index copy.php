<?php
// lang.php — banglascript PHP Runner with Extended Operators & Loop Conditions

// 1. Default banglascript program and scan‐input
$defaultCode = <<<'CODE'
box a = 5;
box b = 3;
box c = 0;

// arithmetic, modulo, relational, logical
box sum   = a + b;
box diff  = a - b;
box prod  = a * b;
box quot  = a / b;
box mod   = a % b;
box eq    = (a == b);
box neq1  = (a != b);
box neq2  = (a <> b);
box lt    = (a < b);
box gt    = (a > b);
box le    = (a <= b);
box ge    = (a >= b);
box ngt   = (a !> b);   // same as <=
box nlt   = (a !< b);   // same as >=
box logic = (sum > diff) and (mod != 0) or !(eq);

// conditional
if (logic) {
  output("Logic is true");
} else {
  output("Logic is false");
}

// fixed-count loop
loop(3) {
  output("Count++: " + c);
  c = c + 1;
}

// while-style loop (condition)
box x = 0;
loop(x < 5) {
  output("x = " + x);
  x = x + 1;
}

// comma-separated AND-conditions
box y = 0;
loop(y < 3, sum > 0) {
  output("y = " + y);
  y = y + 1;
}

input(token);
output("You entered: " + token);
CODE;

$defaultScan = "Hello";

// 2. Tokenizer
function tokenize(string $input): array {
    // strip comments
    $input = preg_replace('!//.*!', '', $input);
    $input = preg_replace('!/\*.*?\*/!s', '', $input);

    // multi-char ops, single-char ops, symbols, keywords, strings, numbers, identifiers
    $pattern = '/\s*('
             . '==|!=|<>|<=|>=|!<|!>|&&|\|\||\band\b|\bor\b|'   // multi-char ops
             . '[+\-*\/%<>!]|'                                  // single-char ops (excluding =)
             . '[=]|'                                           // assignment
             . '[\{\}\(\);,]|'                                  // punctuation
             . 'box\b|if\b|else\b|loop\b|output\b|input\b|'     // keywords
             . '"(?:\\\\.|[^"])*"|\'(?:\\\\.|[^\'])*\'|'        // string literals
             . '\d+(?:\.\d+)?|'                                  // numbers
             . '[A-Za-z_]\w*'                                    // identifiers
             . ')/';

    preg_match_all($pattern, $input, $m);
    $raws = $m[1] ?? [];
    $toks = [];

    foreach ($raws as $r) {
        if (in_array($r, ['box','if','else','loop','output','input'], true)) {
            $type = 'KEYWORD';
        }
        elseif (in_array($r, [
            '==','!=','<>','<=','>=','!<','!>',
            '&&','||','and','or','!'
        ], true)) {
            $type = 'OP';
        }
        elseif (in_array($r, ['+','-','*','/','%','<','>'], true)) {
            $type = 'OP';
        }
        elseif ($r === '=') {
            $type = 'SYMBOL';
        }
        elseif (in_array($r, ['{','}','(',')',';','.',','], true)) {
            $type = 'SYMBOL';
        }
        elseif (preg_match('/^\d+(?:\.\d+)?$/', $r)) {
            $type = 'NUMBER';
        }
        elseif (preg_match('/^"(?:\\\\.|[^"])*"$|^\'(?:\\\\.|[^\'])*\'$/', $r)) {
            $type = 'STRING';
            $r = substr($r, 1, -1);
        }
        else {
            $type = 'IDENT';
        }
        $toks[] = ['type'=>$type, 'value'=>$r];
    }

    $toks[] = ['type'=>'EOF', 'value'=>null];
    return $toks;
}

// 3. Parser
class Parser {
    private array $T;
    private int   $p = 0;

    public function __construct(array $tokens) {
        $this->T = $tokens;
    }

    private function peek(): array {
        return $this->T[$this->p];
    }

    private function next(): array {
        return $this->T[$this->p++];
    }

    private function eat(string $type, string $val = null): array {
        $tk = $this->peek();
        if ($tk['type'] === $type && ($val === null || $tk['value'] === $val)) {
            return $this->next();
        }
        $got = $tk['value'] ?? $tk['type'];
        $exp = $val ?? $type;
        throw new Exception("Expected “{$exp}” but got “{$got}”");
    }

    public function parseProgram(): array {
        $stmts = [];
        while ($this->peek()['type'] !== 'EOF') {
            $stmts[] = $this->parseStmt();
        }
        return $stmts;
    }

    private function parseStmt(): array {
        $tk = $this->peek();
        if ($tk['type']==='KEYWORD' && $tk['value']==='box') {
            $s = $this->parseVarDecl();
            $this->eat('SYMBOL',';');
            return $s;
        }
        if ($tk['type']==='IDENT') {
            $s = $this->parseAssign();
            $this->eat('SYMBOL',';');
            return $s;
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='if') {
            return $this->parseIf();
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='loop') {
            return $this->parseLoop();
        }
        if ($tk['type']==='KEYWORD' && in_array($tk['value'], ['output','input'], true)) {
            $s = $this->parseIo();
            $this->eat('SYMBOL',';');
            return $s;
        }
        throw new Exception("Unknown statement start “{$tk['value']}”");
    }

    private function parseVarDecl(): array {
        $this->eat('KEYWORD','box');
        $id  = $this->eat('IDENT');
        $this->eat('SYMBOL','=');
        $expr = $this->parseExpr();
        return ['type'=>'varDecl','name'=>$id['value'],'expr'=>$expr];
    }

    private function parseAssign(): array {
        $id  = $this->eat('IDENT');
        $this->eat('SYMBOL','=');
        $expr = $this->parseExpr();
        return ['type'=>'assign','name'=>$id['value'],'expr'=>$expr];
    }

    private function parseIf(): array {
        $this->eat('KEYWORD','if');
        $this->eat('SYMBOL','(');
        $cond = $this->parseExpr();
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $then = [];
          while ($this->peek()['value'] !== '}') {
              $then[] = $this->parseStmt();
          }
        $this->eat('SYMBOL','}');
        $else = null;
        if ($this->peek()['type']==='KEYWORD' && $this->peek()['value']==='else') {
            $this->eat('KEYWORD','else');
            $this->eat('SYMBOL','{');
              $else = [];
              while ($this->peek()['value'] !== '}') {
                  $else[] = $this->parseStmt();
              }
            $this->eat('SYMBOL','}');
        }
        return ['type'=>'if','cond'=>$cond,'then'=>$then,'else'=>$else];
    }

    private function parseLoop(): array {
        $this->eat('KEYWORD','loop');
        $this->eat('SYMBOL','(');
          $conds = [$this->parseExpr()];
          while ($this->peek()['value'] === ',') {
              $this->eat('SYMBOL',',');
              $conds[] = $this->parseExpr();
          }
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $body = [];
          while ($this->peek()['value'] !== '}') {
              $body[] = $this->parseStmt();
          }
        $this->eat('SYMBOL','}');
        return ['type'=>'loop','conds'=>$conds,'body'=>$body];
    }

    private function parseIo(): array {
        $tk = $this->next();
        $this->eat('SYMBOL','(');
        if ($tk['value'] === 'output') {
            $expr = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return ['type'=>'output','expr'=>$expr];
        }
        // input
        $id = $this->eat('IDENT');
        $this->eat('SYMBOL',')');
        return ['type'=>'input','name'=>$id['value']];
    }

    // Expression grammar
    private function parseExpr(): array {
        return $this->parseOr();
    }

    private function parseOr(): array {
        $left = $this->parseAnd();
        while ($this->peek()['type'] === 'OP' &&
               in_array($this->peek()['value'], ['||','or'], true)) {
            $op = $this->next()['value'];
            $right = $this->parseAnd();
            $left = ['type'=>'binop','op'=>$op,'left'=>$left,'right'=>$right];
        }
        return $left;
    }

    private function parseAnd(): array {
        $left = $this->parseComp();
        while ($this->peek()['type'] === 'OP' &&
               in_array($this->peek()['value'], ['&&','and'], true)) {
            $op = $this->next()['value'];
            $right = $this->parseComp();
            $left = ['type'=>'binop','op'=>$op,'left'=>$left,'right'=>$right];
        }
        return $left;
    }

    private function parseComp(): array {
        $left = $this->parseAdd();
        if ($this->peek()['type'] === 'OP' &&
            in_array($this->peek()['value'], ['==','!=','<>','<','>','<=','>=','!<','!>'], true)
        ) {
            $op = $this->next()['value'];
            $right = $this->parseAdd();
            $left = ['type'=>'binop','op'=>$op,'left'=>$left,'right'=>$right];
        }
        return $left;
    }

    private function parseAdd(): array {
        $left = $this->parseMul();
        while ($this->peek()['value'] === '+' || $this->peek()['value'] === '-') {
            $op = $this->next()['value'];
            $right = $this->parseMul();
            $left = ['type'=>'binop','op'=>$op,'left'=>$left,'right'=>$right];
        }
        return $left;
    }

    private function parseMul(): array {
        $left = $this->parseUn();
        while (in_array($this->peek()['value'], ['*','/','%'], true)) {
            $op = $this->next()['value'];
            $right = $this->parseUn();
            $left = ['type'=>'binop','op'=>$op,'left'=>$left,'right'=>$right];
        }
        return $left;
    }

    private function parseUn(): array {
        if (in_array($this->peek()['value'], ['!','-'], true)) {
            $op = $this->next()['value'];
            $expr = $this->parseUn();
            return ['type'=>'unary','op'=>$op,'expr'=>$expr];
        }
        return $this->parseFactor();
    }

    private function parseFactor(): array {
        $tk = $this->peek();
        if ($tk['type']==='SYMBOL' && $tk['value']==='(') {
            $this->eat('SYMBOL','(');
            $expr = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return $expr;
        }
        if ($tk['type']==='NUMBER') {
            $val = floatval($this->next()['value']);
            return ['type'=>'number','value'=>$val];
        }
        if ($tk['type']==='STRING') {
            $val = $this->next()['value'];
            return ['type'=>'string','value'=>$val];
        }
        if ($tk['type']==='IDENT') {
            $val = $this->next()['value'];
            return ['type'=>'ident','name'=>$val];
        }
        throw new Exception("Unexpected factor “{$tk['value']}”");
    }
}

// 4. Interpreter
function runProgram(array $ast, array $inputs): array {
    $env     = [];
    $out     = [];
    $scanIdx = 0;

    $eval = null;
    $eval = function($node) use (&$eval, &$env) {
        switch ($node['type']) {
            case 'number': return $node['value'];
            case 'string': return $node['value'];
            case 'ident':  return $env[$node['name']] ?? 0;
            case 'unary':
                $x = $eval($node['expr']);
                return $node['op'] === '!' ? (!$x ? 1 : 0)
                                           : -$x;
            case 'binop':
                $L = $eval($node['left']);
                $R = $eval($node['right']);
                switch ($node['op']) {
                    case '+':  return (is_string($L)||is_string($R)) ? ($L.$R) : $L + $R;
                    case '-':  return $L - $R;
                    case '*':  return $L * $R;
                    case '/':  return $L / $R;
                    case '%':  return $L % $R;
                    case '==': return $L == $R;
                    case '!=':
                    case '<>': return $L != $R;
                    case '<':  return $L <  $R;
                    case '>':  return $L >  $R;
                    case '<=': return $L <= $R;
                    case '>=': return $L >= $R;
                    case '!<': return $L >= $R;
                    case '!>': return $L <= $R;
                    case '&&':
                    case 'and': return ($L && $R) ? 1 : 0;
                    case '||':
                    case 'or':  return ($L || $R) ? 1 : 0;
                }
        }
        return 0;
    };

    $exec = function($stmt) use (&$exec, &$eval, &$env, &$out, &$inputs, &$scanIdx) {
        switch ($stmt['type']) {
            case 'varDecl':
                $env[$stmt['name']] = $eval($stmt['expr']); break;
            case 'assign':
                $env[$stmt['name']] = $eval($stmt['expr']); break;
            case 'output':
                $out[] = $eval($stmt['expr']); break;
            case 'input':
                $tok = $inputs[$scanIdx++] ?? '';
                $env[$stmt['name']] = is_numeric($tok) ? +$tok : $tok;
                break;
            case 'if':
                if ($eval($stmt['cond'])) {
                    foreach ($stmt['then'] as $_) $exec($_);
                } elseif ($stmt['else']) {
                    foreach ($stmt['else'] as $_) $exec($_);
                }
                break;
            case 'loop':
                $conds = $stmt['conds'];
                // fixed-count if single number literal
                if (count($conds) === 1 && $conds[0]['type'] === 'number') {
                    $n = intval($eval($conds[0]));
                    for ($i = 0; $i < $n; $i++) {
                        foreach ($stmt['body'] as $_) $exec($_);
                    }
                }
                // else while-style
                else {
                    while (true) {
                        $ok = true;
                        foreach ($conds as $c) {
                            if (!$eval($c)) { $ok = false; break; }
                        }
                        if (!$ok) break;
                        foreach ($stmt['body'] as $_) $exec($_);
                    }
                }
                break;
        }
    };

    foreach ($ast as $s) $exec($s);
    return $out;
}

// 5. Handle form submission
$error  = '';
$output = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code   = $_POST['code'] ?? '';
    $scan   = trim($_POST['scan'] ?? '');
    $inputs = $scan === '' ? [] : preg_split('/\s+/', $scan);

    try {
        $toks    = tokenize($code);
        $parser  = new Parser($toks);
        $ast     = $parser->parseProgram();
        $output  = runProgram($ast, $inputs);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>banglascript PHP Runner</title>
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
    <h1 class="mb-4 text-center">Bangla Script Runner</h1>

    <div class="mb-4 text-center">
      <a href="bnf/" class="btn btn-secondary me-2">BNF</a>
      <a href="example/" class="btn btn-secondary me-2">Example</a>
      <a href="contact/" class="btn btn-secondary">Contact</a>
    </div>
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

