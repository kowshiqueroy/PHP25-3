<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Quizzes</title></head>
<body>
<h2>Quizzes</h2>
<form method="post">
<?php
$result = $conn->query("SELECT * FROM quizzes");
while($row = $result->fetch_assoc()) {
  echo "<p><strong>{$row['question']}</strong></p>";
  echo "<input type='radio' name='q{$row['id']}' value='A'> {$row['option_a']}<br>";
  echo "<input type='radio' name='q{$row['id']}' value='B'> {$row['option_b']}<br>";
  echo "<input type='radio' name='q{$row['id']}' value='C'> {$row['option_c']}<br>";
  echo "<input type='radio' name='q{$row['id']}' value='D'> {$row['option_d']}<br><hr>";
}
?>
<input type="submit" value="Submit Answers">
</form>
</body>
</html>