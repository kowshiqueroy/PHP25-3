<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bangla Script Code Examples</title>
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
 <h1 class="mb-4 text-center">Bangla Script Code Examples</h1>
  <div style="text-align: center;">
    <a href="javascript:history.back()" class="back-btn">Back</a>
  </div>
<div class="grammar-box">
  <div class="category">1. Simple Code Examples</div>
  <!-- Simple examples -->
  <div class="code-card"><div class="title">Hello World</div><div class="desc">Prints a greeting message.</div><pre>output("Hello, World!");</pre></div>
  <div class="code-card"><div class="title">Greet by Name</div><div class="desc">Reads your name and greets you.</div><pre>input(name);
output("Hi " + name + "!");</pre></div>
  <div class="code-card"><div class="title">Sum of Two Numbers</div><div class="desc">Adds two inputs.</div><pre>input(a);
input(b);
box sum = a + b;
output("Sum = " + sum);</pre></div>
  <div class="code-card"><div class="title">Even or Odd</div><div class="desc">Checks parity.</div><pre>input(n);
if (n % 2 == 0) {
  output("Even");
} else {
  output("Odd");
}</pre></div>
  <div class="code-card"><div class="title">Maximum of Two</div><div class="desc">Finds the larger of two.</div><pre>input(a);
input(b);
if (a > b) {
  output("Max = " + a);
} else {
  output("Max = " + b);
}</pre></div>
  <div class="code-card"><div class="title">Simple Counter Loop</div><div class="desc">Prints 1 through 5.</div><pre>box i = 1;
loop(5) {
  output(i);
  i = i + 1;
}</pre></div>
  <div class="code-card"><div class="title">Sum First N Numbers</div><div class="desc">Accumulates 1+…+n.</div><pre>input(n);
box sum = 0;
box i = 1;
loop(n) {
  sum = sum + i;
  i = i + 1;
}
output("Total = " + sum);</pre></div>
  <div class="code-card"><div class="title">Factorial (Iterative)</div><div class="desc">Computes n!.</div><pre>input(n);
box fact = 1;
box i = 1;
loop(n) {
  fact = fact * i;
  i = i + 1;
}
output("n! = " + fact);</pre></div>
  <div class="code-card"><div class="title">Multiplication Table</div><div class="desc">Prints table up to 10.</div><pre>input(n);
box i = 1;
loop(10) {
  output(n + "×" + i + " = " + (n * i));
  i = i + 1;
}</pre></div>
  <div class="code-card"><div class="title">Countdown</div><div class="desc">Counts down to 1.</div><pre>input(n);
box i = n;
loop(i) {
  output(i);
  i = i - 1;
}</pre></div>
  <div class="code-card"><div class="title">Celsius to Fahrenheit</div><div class="desc">Temperature conversion.</div><pre>input(c);
box f = c * 9 / 5 + 32;
output("F = " + f);</pre></div>
  <div class="code-card"><div class="title">Absolute Value</div><div class="desc">Uses conditional operator.</div><pre>input(x);
box abs = x < 0 ? -x : x;
output("Absolute = " + abs);</pre></div>
  <div class="code-card"><div class="title">Swap Two Variables</div><div class="desc">Swaps values via a temp.</div><pre>input(a);
input(b);
box temp = a;
a = b;
b = temp;
output("a=" + a + " b=" + b);</pre></div>
  <div class="code-card"><div class="title">Average of Three</div><div class="desc">Computes mean.</div><pre>input(a);
input(b);
input(c);
box avg = (a + b + c) / 3;
output("Avg = " + avg);</pre></div>
  <div class="code-card"><div class="title">Simple Greeting Loop</div><div class="desc">Greets thrice.</div><pre>input(name);
loop(3) {
  output("Hello, " + name);
}</pre></div>
  <div class="code-card"><div class="title">Sum of Digits</div><div class="desc">Extracts digits iteratively.</div><pre>input(n);
box sum = 0;
loop(n > 0) {
  sum = sum + (n % 10);
  n = n / 10;
}
output("Digit sum = " + sum);</pre></div>
  <div class="code-card"><div class="title">Check Positive/Negative</div><div class="desc">Three-way check.</div><pre>input(x);
if (x > 0) output("Positive");
else if (x < 0) output("Negative");
else output("Zero");</pre></div>
  <div class="code-card"><div class="title">Simple Concatenation</div><div class="desc">Joins two strings.</div><pre>input(s1);
input(s2);
output(s1 + " " + s2);</pre></div>
  <div class="code-card"><div class="title">Character Echo</div><div class="desc">Repeats a character.</div><pre>input(ch);
loop(5) {
  output(ch);
}</pre></div>
  <div class="code-card"><div class="title">Swap Without Temp</div><div class="desc">Swaps via arithmetic.</div><pre>input(a);
input(b);
a = a + b;
b = a - b;
a = a - b;
output("a=" + a + " b=" + b);</pre></div>

  <div class="category">2. Complex Code Examples</div>
  <!-- Complex examples -->
  <div class="code-card"><div class="title">GCD via Euclid’s Algorithm</div><div class="desc">Iteratively computes greatest common divisor.</div><pre>input(a);
input(b);
loop(b != 0) {
  box r = a % b;
  a = b;
  b = r;
}
output("GCD = " + a);</pre></div>
  <div class="code-card"><div class="title">LCM from GCD</div><div class="desc">Derives least common multiple.</div><pre>input(a);
input(b);
box gcd = 1;
loop(b != 0) {
  box r = a % b;
  a = b;
  b = r;
}
box lcm = a * (a / gcd);
output("LCM = " + lcm);</pre></div>
  <div class="code-card"><div class="title">Factorial</div><div class="desc">Iteratively computes factorial.</div><pre>input(n);
box fact = 1;
loop(n > 0) {
  fact = fact * n;
  n = n - 1;
}
output("Factorial = " + fact);</pre></div>
  <div class="code-card"><div class="title">Fibonacci</div><div class="desc">Iteratively computes Fibonacci sequence.</div><pre>input(n);
box f0 = 0;
box f1 = 1;
loop(n > 0) {
  box f2 = f0 + f1;
  f0 = f1;
  f1 = f2;
  n = n - 1;
}
output("Fibonacci = " + f0);</pre></div>
</div>
</div>
</div>
</body>
</html>
