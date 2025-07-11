<?php
include('../header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bangla Script Grammar</title>
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
    .code-card {
      background-color: #1e1e1e;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
    }
    .code-card .title {
      color: #76ff03;
      font-weight: bold;
    }
    .code-card .desc {
      color: #999;
    }
    .code-card pre {
      background: none;
      color: #76ff03;
    }
    .grammar-box {
      background-color: #1e1e1e;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
    }
    .category {
      color: #76ff03;
      font-weight: bold;
    }
    .back-btn {
      display: inline-block;
      padding: 10px 20px;
      background-color: #76ff03;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div style="text-align: center;">
        <h1>Bangla Script BNF Grammar</h1>
        <a href="javascript:history.back()" class="back-btn" style="display: inline-block; margin-top: 20px;">Back</a>
    </div>
    <div class="grammar-box">
      <div class="category">&lt;program&gt;</div>
      <pre>::= { &lt;statement&gt; }</pre>
      
      <div class="category">&lt;statement&gt;</div>
      <pre>::= &lt;varDecl&gt; ";"
                   | &lt;assignment&gt; ";"
                   | &lt;ifStmt&gt;
                   | &lt;loopStmt&gt;
                   | &lt;ioStmt&gt; ";"</pre>

      <div class="category">&lt;varDecl&gt;</div>
      <pre>::= "box" &lt;ident&gt; "=" &lt;expr&gt;</pre>

      <div class="category">&lt;assignment&gt;</div>
      <pre>::= &lt;ident&gt; "=" &lt;expr&gt;</pre>

      <div class="category">&lt;ifStmt&gt;</div>
      <pre>::= "if" "(" &lt;expr&gt; ")" "{" { &lt;statement&gt; } "}"
                   [ "else" "{" { &lt;statement&gt; } "}" ]</pre>

      <div class="category">&lt;loopStmt&gt;</div>
      <pre>::= "loop" "(" &lt;condList&gt; ")" "{" { &lt;statement&gt; } "}"</pre>

      <div class="category">&lt;condList&gt;</div>
      <pre>::= &lt;expr&gt; { "," &lt;expr&gt; }</pre>

      <div class="category">&lt;ioStmt&gt;</div>
      <pre>::= "output" "(" &lt;expr&gt; ")"
                   | "input" "(" &lt;ident&gt; ")"</pre>

      <div class="category">&lt;expr&gt;</div>
      <pre>::= &lt;orExpr&gt;</pre>

      <div class="category">&lt;orExpr&gt;</div>
      <pre>::= &lt;andExpr&gt; { ( "||" | "or" ) &lt;andExpr&gt; }</pre>

      <div class="category">&lt;andExpr&gt;</div>
      <pre>::= &lt;compExpr&gt; { ( "&&" | "and" ) &lt;compExpr&gt; }</pre>

      <div class="category">&lt;compExpr&gt;</div>
      <pre>::= &lt;addExpr&gt; [ ( "==" | "!=" | "&lt;&gt;" | "&lt;" | "&gt;" | "&lt;=" | "&gt;=" | "!&lt;" | "!&gt;" ) &lt;addExpr&gt; ]</pre>

      <div class="category">&lt;addExpr&gt;</div>
      <pre>::= &lt;mulExpr&gt; { ( "+" | "-" ) &lt;mulExpr&gt; }</pre>

      <div class="category">&lt;mulExpr&gt;</div>
      <pre>::= &lt;unaryExpr&gt; { ( "*" | "/" | "%" ) &lt;unaryExpr&gt; }</pre>

      <div class="category">&lt;unaryExpr&gt;</div>
      <pre>::= ( "!" | "-" ) &lt;unaryExpr&gt; | &lt;factor&gt;</pre>

      <div class="category">&lt;factor&gt;</div>
      <pre>::= &lt;number&gt;
                   | &lt;string&gt;
                   | &lt;ident&gt;
                   | "(" &lt;expr&gt; ")"</pre>

      <div class="category">&lt;number&gt;</div>
      <pre>::= &lt;digits&gt; [ "." &lt;digits&gt; ]</pre>

      <div class="category">&lt;string&gt;</div>
      <pre>::= "\"" { &lt;char&gt; } "\"" | "'" { &lt;char&gt; } "'"</pre>

      <div class="category">&lt;ident&gt;</div>
      <pre>::= &lt;letter&gt; { &lt;letter&gt; | &lt;digit&gt; | "_" }</pre>

      <div class="category">&lt;letter&gt;</div>
      <pre>::= "A"–"Z" | "a"–"z"</pre>

      <div class="category">&lt;digit&gt;</div>
      <pre>::= "0"–"9"</pre>
    </div>

    <div class="category">- Basic Tutorial Guide</div>
    <div class="desc">• Getting Started</div>
    <pre>– Open the runner page. Two boxes appear: the Code editor and the Scan Input field.
– The editor is pre­loaded with a sample program that shows variables, arithmetic, comparisons, loops, logic, and I/O.</pre>
    <div class="desc">• Variables &amp; Expressions</div>
    <pre>– Declare with box name = expr; (numbers, strings, or expressions).
– Reassign with name = expr;.
– Arithmetic: +, -, *, /, %. Strings concatenate with +.</pre>
    <div class="desc">• Comparisons &amp; Logic</div>
    <pre>– Comparisons: ==, !=, <&gt;, &lt;, &gt;, &lt;=, &gt;=, plus !&lt; (>=) and !&gt; (<=).
– Logic: ! for NOT, &&/and for AND, ||/or for OR.</pre>
    <div class="desc">• Control Flow</div>
    <pre>– Conditional:
if (condition) { …   } else { …   }
– Loop:
- Fixed count: loop(5) { … } runs 5 times.
- While-style or combined conditions: loop(x &lt; 5) or loop(x&lt;3, y&gt;0) repeats as long as all conditions are true.</pre>
    <div class="desc">• Input &amp; Output</div>
    <pre>– input(var); reads the next token from the Scan Input buffer (space-separated).
– output(expr); appends the value to the result area.</pre>
    <div class="desc">• Running Your Code</div>
    <pre>- Edit or replace the code in the editor.
- Provide any needed input tokens in Scan Input.
- Click Run. The interpreter will parse, evaluate, and display each output(...) result line by line.</pre>
  </div>





  
</body>
</html>
