<?php
session_start(); include "../db.php";
if (!$_SESSION['admin']) die("Access Denied");

// Add quiz
if (isset($_POST['question'])) {
  $stmt = $conn->prepare("INSERT INTO quizzes (question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssss", $_POST['question'], $_POST['a'], $_POST['b'], $_POST['c'], $_POST['d'], $_POST['correct']);
  $stmt->execute();
}

// Delete quiz
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM quizzes WHERE id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
}
?>
<h2>Manage Quizzes</h2>
<form method="post">
  <textarea name="question" placeholder="Question"></textarea><br>
  <input name="a" placeholder="Option A"><br>
  <input name="b" placeholder="Option B"><br>
  <input name="c" placeholder="Option C"><br>
  <input name="d" placeholder="Option D"><br>
  <input name="correct" placeholder="Correct Option (A/B/C/D)"><br>
  <input type="submit" value="Add Quiz">
</form>
<hr>
<?php
$result = $conn->query("SELECT * FROM quizzes");
while($row = $result->fetch_assoc()) {
  echo "<p>{$row['question']} <a href='?delete={$row['id']}'>Delete</a></p>";
}
?>