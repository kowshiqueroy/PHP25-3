<?php
session_start(); include "../db.php";
if (!$_SESSION['admin']) die("Access Denied");

// Add problem
if (isset($_POST['title'], $_POST['desc'], $_POST['link'])) {
  $stmt = $conn->prepare("INSERT INTO problems (title, description, compiler_link) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $_POST['title'], $_POST['desc'], $_POST['link']);
  $stmt->execute();
}

// Delete problem
if (isset($_GET['delete'])) {
  $stmt = $conn->prepare("DELETE FROM problems WHERE id=?");
  $stmt->bind_param("i", $_GET['delete']);
  $stmt->execute();
}
?>
<h2>Manage Problems</h2>
<form method="post">
  <input name="title" placeholder="Title"><br>
  <textarea name="desc" placeholder="Description"></textarea><br>
  <input name="link" placeholder="JDoodle Embed Link"><br>
  <input type="submit" value="Add Problem">
</form>
<hr>
<?php
$result = $conn->query("SELECT * FROM problems");
while($row = $result->fetch_assoc()) {
  echo "<p>{$row['title']} <a href='?delete={$row['id']}'>Delete</a></p>";
}
?>