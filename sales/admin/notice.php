<?php
require_once '../conn.php';
require_once 'header.php';




?>

<div class="card p-1 text-center">Notice</div>
  <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" style="max-width: 500px; margin: 0 auto; text-align: center;">
    <div class="form-group">
        <label for="content">Content</label>
        <textarea class="form-control" id="content" name="content" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Content</th>
            <th>Datetime</th>
            <th>Control</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * FROM notice ORDER BY id DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . $row['username'] . "</td><td>" . $row['content'] . "</td><td>" . $row['datetime'] . "</td>
            <td>
                <a href='notice.php?do=Delete&id=" . $row['id'] . "' class='btn btn-danger confirm'><i class='fa fa-close'></i> Delete </a>
            </td></tr>";
        }
    }
    ?>
    </tbody>
</table>

<?php
if (isset($_GET['do'])) {
    $do = $_GET['do'];
} else {
    $do = 'Create';
}

if ($do == 'Create') {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $username = $_SESSION['username'];
        $content = $_POST['content'];

        $sql = "SELECT * FROM notice WHERE content = '$content' ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['content'] == $content) {
                echo "<script>window.location.href='notice.php';</script>";
                exit();
            }
        }

        $sql = "INSERT INTO notice (username, content, datetime) VALUES ('$username', '$content', NOW())";
        if ($conn->query($sql) === TRUE) {
            echo "<script>window.location.href='notice.php';</script>";
        } else {
           echo "<script>window.location.href='notice.php';</script>";
        }

    }

    ?>

  

    <?php

} elseif ($do == 'Delete') {

    $id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;

    $sql = "DELETE FROM notice WHERE id = $id";
    $stmt = $conn->query($sql);

    if ($stmt) {
         echo "<script>window.location.href='notice.php';</script>";
    } else {
        echo "<script>window.location.href='notice.php';</script>";;
    }

}
?>
<?php
require_once 'footer.php';
?>

