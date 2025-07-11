<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head><title>Problems</title></head>
<body>
<h2>Practice Problems</h2>
<?php
$result = $conn->query("SELECT * FROM problems");
while($row = $result->fetch_assoc()) {
  echo "<h4>{$row['title']}</h4>";
  echo "<p>{$row['description']}</p>";
  echo "<iframe src='{$row['compiler_link']}' height='600' width='100%' frameborder='0'></iframe><hr>";
}
?>
</body>
</html>