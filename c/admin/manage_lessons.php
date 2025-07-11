<?php
session_start(); include "../db.php";
if (!$_SESSION['admin']) die("Access Denied");

// Add lesson
if (isset($_POST['title'], $_POST['content'])) {
  $stmt = $conn->prepare("INSERT INTO lessons (title, content) VALUES (?, ?)");
  $stmt->bind_param("ss", $_POST['title'], $_POST['content']);
  $stmt->execute();
}

// Delete lesson
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM lessons WHERE id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
}
?>
<h2>Manage Lessons</h2>
<form method="post">
  <input name="title" placeholder="Title"><br>
  <textarea name="content" placeholder="Content"></textarea><br>
  <input type="submit" value="Add Lesson">
</form>
<hr>
<?php
$result = $conn->query("SELECT * FROM lessons");
while($row = $result->fetch_assoc()) {
  echo "<h4>{$row['title']}</h4>";
  echo "<a href='?delete={$row['id']}'>Delete</a><hr>";
}
?>