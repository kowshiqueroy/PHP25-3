<?php
// lang.php — EduBox PHP Runner with Comments Stripped & Extended Ops

// 1. Default EduBox program + scan‐input
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

// fixed‐count loop
loop(3) {
  output("Count++: " + c);
  c = c + 1;
}

// while‐style loop (condition)
box x = 0;
loop(x < 5) {
  output("x = " + x);
  x = x + 1;
}

// comma‐separated AND conditions
box y = 0;
loop(y < 3, sum > 0) {
  output("y = " + y);
  y = y + 1;
}

input(token);
output("You entered: " + token);
CODE;

$defaultScan = "Hello";

// 2. Tokenizer: strip comments first
function tokenize(string $input): array {
    // remove line comments //
    $input = preg_replace('!//.*!', '', $input);
    // remove block comments /* */
    $input = preg_replace('!/\*.*?\*/!s', '', $input);

    $pattern = '/\s*('
             . '==|!=|<>|<=|>=|!<|!>|&&|\|\||\band\b|\bor\b|'  // multi‐char ops
             . '[\{\}\(\);,=+\-\*\/%<>!]|'                    // single‐char symbols
             . '\bbox\b|\bif\b|\belse\b|\bloop\b|\boutput\b|\binput\b|' 
             . '"(?:\\\\.|[^"])*"|\'(?:\\\\.|[^\'])*\'|'      // strings
             . '\d+(?:\.\d+)?|'                              // numbers
             . '[A-Za-z_]\w*'                                // identifiers
             . ')/';

    preg_match_all($pattern, $input, $m);
    $raws = $m[1] ?? [];
    $toks = [];

    foreach ($raws as $r) {
        if (in_array($r, ['box','if','else','loop','output','input'], true)) {
            $type = 'KEYWORD';
        }
        elseif (in_array($r, ['==','!=','<>','<=','>=','!<','!>','&&','||','and','or'], true)) {
            $type = 'OP';
        }
        elseif (in_array($r, ['{','}','(',')',';','.',',','+','-','*','/','%','<','>','=','!'], true)) {
            $type = 'SYMBOL';
        }
        elseif (preg_match('/^\d+(?:\.\d+)?$/', $r)) {
            $type = 'NUMBER';
        }
        elseif (preg_match('/^"(?:\\\\.|[^"])*"$|^\'(?:\\\\.|[^\'])*\'$/', $r)) {
            $type = 'STRING';
            $r = substr($r,1,-1);
        }
        else {
            $type = 'IDENT';
        }
        $toks[] = ['type'=>$type,'value'=>$r];
    }
    $toks[] = ['type'=>'EOF','value'=>null];
    return $toks;
}

// 3. Parser (unchanged)…

// 4. Interpreter (unchanged)…

// 5. Handle form submission & render HTML…
// 3. Parser with extended expression grammar
class Parser {
    private array $T; private int $p = 0;
    public function __construct(array $toks) { $this->T = $toks; }
    private function peek(): array { return $this->T[$this->p]; }
    private function next(): array { return $this->T[$this->p++]; }
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
        $S = [];
        while ($this->peek()['type'] !== 'EOF') {
            $S[] = $this->parseStmt();
        }
        return $S;
    }

    private function parseStmt(): array {
        $tk = $this->peek();
        if ($tk['type']==='KEYWORD' && $tk['value']==='box') {
            $s = $this->parseVarDecl(); $this->eat('SYMBOL',';'); return $s;
        }
        if ($tk['type']==='IDENT') {
            $s = $this->parseAssign();  $this->eat('SYMBOL',';'); return $s;
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='if') {
            return $this->parseIf();
        }
        if ($tk['type']==='KEYWORD' && $tk['value']==='loop') {
            return $this->parseLoop();
        }
        if ($tk['type']==='KEYWORD' && in_array($tk['value'],['output','input'],true)) {
            $s = $this->parseIo(); $this->eat('SYMBOL',';'); return $s;
        }
        throw new Exception("Unknown statement start “{$tk['value']}”");
    }

    private function parseVarDecl(): array {
        $this->eat('KEYWORD','box');
        $id = $this->eat('IDENT');
        $this->eat('SYMBOL','=');
        $e  = $this->parseExpr();
        return ['type'=>'varDecl','name'=>$id['value'],'expr'=>$e];
    }

    private function parseAssign(): array {
        $id = $this->eat('IDENT');
        $this->eat('SYMBOL','=');
        $e  = $this->parseExpr();
        return ['type'=>'assign','name'=>$id['value'],'expr'=>$e];
    }

    private function parseIf(): array {
        $this->eat('KEYWORD','if');
        $this->eat('SYMBOL','(');
          $c = $this->parseExpr();
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $th = [];
          while ($this->peek()['value']!=='}') {
            $th[] = $this->parseStmt();
          }
        $this->eat('SYMBOL','}');
        $el = null;
        if ($this->peek()['type']==='KEYWORD' && $this->peek()['value']==='else') {
          $this->eat('KEYWORD','else');
          $this->eat('SYMBOL','{');
            $el = [];
            while ($this->peek()['value']!=='}') {
              $el[] = $this->parseStmt();
            }
          $this->eat('SYMBOL','}');
        }
        return ['type'=>'if','cond'=>$c,'then'=>$th,'else'=>$el];
    }

    private function parseLoop(): array {
        $this->eat('KEYWORD','loop');
        $this->eat('SYMBOL','(');
          $conds = [$this->parseExpr()];
          while ($this->peek()['value']===',') {
            $this->eat('SYMBOL',',');
            $conds[] = $this->parseExpr();
          }
        $this->eat('SYMBOL',')');
        $this->eat('SYMBOL','{');
          $bd = [];
          while ($this->peek()['value']!=='}') {
            $bd[] = $this->parseStmt();
          }
        $this->eat('SYMBOL','}');
        return ['type'=>'loop','conds'=>$conds,'body'=>$bd];
    }

    private function parseIo(): array {
        $tk = $this->next();
        $this->eat('SYMBOL','(');
        if ($tk['value']==='output') {
            $e = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return ['type'=>'output','expr'=>$e];
        }
        // input
        $id = $this->eat('IDENT');
        $this->eat('SYMBOL',')');
        return ['type'=>'input','name'=>$id['value']];
    }

    // Expression: OR → AND → COMP → ADD → MUL → UNARY → FACTOR
    private function parseExpr() { return $this->parseOr(); }

    private function parseOr() {
        $L = $this->parseAnd();
        while ($this->peek()['type']==='OP' && in_array($this->peek()['value'], ['||','or'], true)) {
            $op = $this->next()['value'];
            $R  = $this->parseAnd();
            $L  = ['type'=>'binop','op'=>$op,'left'=>$L,'right'=>$R];
        }
        return $L;
    }

    private function parseAnd() {
        $L = $this->parseComp();
        while ($this->peek()['type']==='OP' && in_array($this->peek()['value'], ['&&','and'], true)) {
            $op = $this->next()['value'];
            $R  = $this->parseComp();
            $L  = ['type'=>'binop','op'=>$op,'left'=>$L,'right'=>$R];
        }
        return $L;
    }

    private function parseComp() {
        $L = $this->parseAdd();
        if ($this->peek()['type']==='OP' &&
            in_array($this->peek()['value'], ['==','!=','<>','<','>','<=','>=','!<','!>'], true)
        ) {
            $op = $this->next()['value'];
            $R  = $this->parseAdd();
            $L  = ['type'=>'binop','op'=>$op,'left'=>$L,'right'=>$R];
        }
        return $L;
    }

    private function parseAdd() {
        $L = $this->parseMul();
        while (in_array($this->peek()['value'], ['+','-'], true)) {
            $op = $this->next()['value'];
            $R  = $this->parseMul();
            $L  = ['type'=>'binop','op'=>$op,'left'=>$L,'right'=>$R];
        }
        return $L;
    }

    private function parseMul() {
        $L = $this->parseUn();
        while (in_array($this->peek()['value'], ['*','/','%'], true)) {
            $op = $this->next()['value'];
            $R  = $this->parseUn();
            $L  = ['type'=>'binop','op'=>$op,'left'=>$L,'right'=>$R];
        }
        return $L;
    }

    private function parseUn() {
        if (in_array($this->peek()['value'], ['!','-'], true)) {
            $op = $this->next()['value'];
            $e  = $this->parseUn();
            return ['type'=>'unary','op'=>$op,'expr'=>$e];
        }
        return $this->parseFactor();
    }

    private function parseFactor() {
        $tk = $this->peek();
        if ($tk['type']==='SYMBOL' && $tk['value']==='(') {
            $this->eat('SYMBOL','(');
            $e = $this->parseExpr();
            $this->eat('SYMBOL',')');
            return $e;
        }
        if ($tk['type']==='NUMBER') {
            $v = floatval($this->next()['value']);
            return ['type'=>'number','value'=>$v];
        }
        if ($tk['type']==='STRING') {
            $v = $this->next()['value'];
            return ['type'=>'string','value'=>$v];
        }
        if ($tk['type']==='IDENT') {
            $v = $this->next()['value'];
            return ['type'=>'ident','name'=>$v];
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
    $eval = function($n) use (&$eval,&$env) {
        switch ($n['type']) {
            case 'number': return $n['value'];
            case 'string': return $n['value'];
            case 'ident':  return $env[$n['name']] ?? 0;
            case 'unary':
                $x = $eval($n['expr']);
                return $n['op']==='!' ? (!$x ? 1 : 0) : -$x;
            case 'binop':
                $L = $eval($n['left']);
                $R = $eval($n['right']);
                switch ($n['op']) {
                    case '+':  return is_string($L)||is_string($R) ? ($L.$R) : $L + $R;
                    case '-':  return $L - $R;
                    case '*':  return $L * $R;
                    case '/':  return $L / $R;
                    case '%':  return $L % $R;
                    case '==': return $L == $R;
                    case '!=': case '<>': return $L != $R;
                    case '<':  return $L <  $R;
                    case '>':  return $L >  $R;
                    case '<=': return $L <= $R;
                    case '>=': return $L >= $R;
                    case '!<': return $L >= $R;
                    case '!>': return $L <= $R;
                    case '&&': case 'and': return ($L && $R) ? 1 : 0;
                    case '||': case 'or':  return ($L || $R) ? 1 : 0;
                }
        }
        return 0;
    };

    $exec = function($s) use (&$exec,&$eval,&$env,&$out,&$inputs,&$scanIdx) {
        switch ($s['type']) {
            case 'varDecl':
                $env[$s['name']] = $eval($s['expr']); break;
            case 'assign':
                $env[$s['name']] = $eval($s['expr']); break;
            case 'output':
                $out[] = $eval($s['expr']); break;
            case 'input':
                $tok = $inputs[$scanIdx++] ?? '';
                $env[$s['name']] = is_numeric($tok) ? +$tok : $tok; break;
            case 'if':
                if ($eval($s['cond'])) {
                    foreach ($s['then'] as $_) $exec($_);
                } elseif ($s['else']) {
                    foreach ($s['else'] as $_) $exec($_);
                }
                break;
            case 'loop':
                $conds = $s['conds'];
                // fixed‐count if single numeric
                if (count($conds)===1 && is_numeric($eval($conds[0]))) {
                    $n = intval($eval($conds[0]));
                    for ($i=0; $i<$n; $i++) {
                        foreach ($s['body'] as $_) $exec($_);
                    }
                }
                else {
                    // while‐style: all conditions true
                    while (true) {
                        foreach ($conds as $c) {
                            if (!$eval($c)) { $ok=false; break; }
                            $ok = true;
                        }
                        if (!$ok) break;
                        foreach ($s['body'] as $_) $exec($_);
                    }
                }
                break;
        }
    };

    foreach ($ast as $st) $exec($st);
    return $out;
}

// 5. Handle form
$error  = '';
$output = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code   = $_POST['code'] ?? '';
    $scan   = trim($_POST['scan'] ?? '');
    $inputs = $scan === '' ? [] : preg_split('/\s+/', $scan);

    try {
        $toks   = tokenize($code);
        $parser = new Parser($toks);
        $ast    = $parser->parseProgram();
        $output = runProgram($ast, $inputs);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduBox PHP Runner</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <style>
    body { background: #f8f9fa; }
    textarea { font-family: monospace; }
    pre      { background: #fff; }
  </style>
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4">EduBox PHP Runner</h1>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Code</label>
        <textarea name="code" class="form-control" rows="12"><?php
          echo htmlspecialchars($_POST['code'] ?? $defaultCode);
        ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Scan Input</label>
        <input name="scan" class="form-control"
               value="<?php echo htmlspecialchars($_POST['scan'] ?? $defaultScan) ?>">
      </div>
      <button class="btn btn-primary">Run</button>
    </form>

    <div class="mt-4">
      <h4>Output</h4>
      <pre class="p-3 border" style="min-height:150px">
<?php
if ($error) {
    echo "Error: " . htmlspecialchars($error);
} else {
    echo htmlspecialchars(implode("\n", $output));
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