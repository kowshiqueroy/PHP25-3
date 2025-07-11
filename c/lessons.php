<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Lessons</title></head>
<body>
<h2>Lessons</h2>
<?php
$result = $conn->query("SELECT * FROM lessons");
while($row = $result->fetch_assoc()) {
  echo "<h4>{$row['title']}</h4>";
  echo "<p>{$row['content']}</p><hr>";
}
?>
</body>
</html>