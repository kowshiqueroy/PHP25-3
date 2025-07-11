<?php
include('../header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bangla Script Lesson</title>
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
    .code-card, .grammar-box {
      background-color: #1e1e1e;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
    }
    .code-card .title, .category {
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
      <!-- All lessons start here -->
      <pre>
// 🧑‍🏫 Lesson 1: Your First Output
output("Hello EduBox!");

// 📦 Lesson 2: Variables and Values
box name = "Sky";
box age = 12;
output("Name: " + name);
output("Age: " + age);

// ➕ Lesson 3: Doing Math
box a = 8;
box b = 3;
box sum = a + b;
box product = a * b;
output("Sum: " + sum);
output("Product: " + product);

// 🔤 Lesson 4: String Fun
box word = "cool";
output(word + " " + word);

// 📥 Lesson 5: Getting User Input
input(name);
output("Hello " + name);

// 🔁 Lesson 6: Looping Things
box count = 1;
loop(5) {
  output("Count = " + count);
  count = count + 1;
}

// 🔄 Lesson 7: Conditional Logic
box score = 85;
if (score >= 60) {
  output("Pass");
} else {
  output("Fail");
}

// 🔣 Lesson 8: Comparison Operators
box x = 10;
if (x != 5) {
  output("x is not 5");
}

// 🧮 Lesson 9: Logical Operators
box age = 16;
box hasID = 1;
if (age >= 18 and hasID) {
  output("Allowed");
} else {
  output("Denied");
}

// 📉 Lesson 10: While-style Loop with Conditions
box n = 5;
loop(n > 0) {
  output("n = " + n);
  n = n - 1;
}

// 🎓 Final Challenge
input(name);
input(n);
box i = 1;
loop(n > 0) {
  output("Hello " + name);
  n = n - 1;
}

// 🧩 Advanced Lesson 1: Custom Calculator
input(a);
input(b);
box result1 = (a + b) * 2;
box result2 = (a * a + b * b) / (a + b);
output("Double sum: " + result1);
output("Weighted average: " + result2);

// 🎰 Advanced Lesson 2: Number Guess Game
box target = 7;
box guess = 0;
loop(guess != target) {
  input(guess);
  if (guess < target) {
    output("Too low");
  } else if (guess > target) {
    output("Too high");
  }
}
output("You guessed it!");

// 🧠 Advanced Lesson 3: Truth Table Evaluator
box A = 1;
box B = 0;
box result = (A or B) and !(A and B);
output("Result = " + result);

// 🎈 Advanced Lesson 4: Countdown with Condition
box x = 10;
box done = 0;
loop(x > 0, done == 0) {
  output("x = " + x);
  x = x - 1;
  if (x == 5) {
    done = 1;
    output("Midway!");
  }
}

// 🧪 Advanced Lesson 5: Basic Function Simulation
input(x);
box sq = x * x;
output("Square = " + sq);
if (sq > 100) {
  output("Large square");
}

// 🔄 Advanced Lesson 6: Loop with Dynamic Count
input(n);
box i = 0;
loop(i < n) {
  output("i = " + i);
  i = i + 1;
}

// ✍️ Advanced Lesson 7: Text Repetition & Framing
input(word);
box count = 1;
loop(5) {
  output(">>> " + word + " <<<");
  count = count + 1;
}

// 🧮 Advanced Lesson 8: Modular Arithmetic Table
input(base);
box i = 1;
loop(10) {
  output(i + " % " + base + " = " + (i % base));
  i = i + 1;
}

// 🚦 Advanced Lesson 9: Multi-condition Filter
input(num);
if (num % 2 == 0 and num > 10) {
  output("Even and big");
} else if (num % 2 != 0 and num < 10) {
  output("Odd and small");
} else {
  output("Doesn't match");
}

// 🎳 Advanced Lesson 10: Table Generator
input(base);
box i = 1;
loop(10) {
  output(base + " × " + i + " = " + (base * i));
  i = i + 1;
}
      </pre>
    </div>
  </div>
</body>
</html>