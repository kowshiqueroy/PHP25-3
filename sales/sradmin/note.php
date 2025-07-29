<?php
require_once '../conn.php';
require_once 'header.php';
?>



<style>
    .note {
        width: 100%;
        
        border: 2px solid #ccc;
        border-radius: 10px;
        overflow: hidden;
        background: white;
        margin: 2px auto;
    }
    .note-header {
        background: #ddd;
        padding: 10px;
        display: flex;
        align-items: center;
    }
    .note-header h3 {
        flex-grow: 1;
    }
    .note-content {
        padding: 10px;
    }
    .note-content .btn {
        display: block;
        margin: 10px auto;
    }
</style>
<?php
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if (strpos($msg, 'Error') !== false) {
        echo "<div class='alert alert-danger' style='text-align: center;' id='alert'>
        $msg
        </div>";
        echo "<script>
        setTimeout(function () {
            document.getElementById('alert').style.display = 'none';
        }, 5000);
        </script>";
    } else {
        echo "<div class='alert alert-success' style='text-align: center;' id='alert'>
        $msg
        </div>";
        echo "<script>
        setTimeout(function () {
            document.getElementById('alert').style.display = 'none';
        }, 5000);
        </script>";
    }
}
?>

<?php
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM note WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='note'>";
        echo "<div class='note-header'>";
        echo "<h3>Edit Note #" . $row['id'] . "</h3>";
            echo "<p style='font-size: 14px; color: #555;'><i class='fa fa-calendar'></i> Created: " . $row['created_at'] . "</p>";
        echo "<p style='font-size: 14px; color: #555;'><i class='fa fa-calendar'></i> Edited: " . $row['modified_at'] . "</p>";
        echo "</div>";
        echo "<div class='note-content'>";
        echo "<form action='note.php' method='post'>";
        echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
        echo "<div class='form-group'>";
        echo "<label for='content'>Content:</label>";
        echo "<textarea class='form-control' name='content' id='content' rows='5'>" . $row['content'] . "</textarea>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary'>Update</button>";
        echo "<a href='note.php' class='btn btn-secondary'>Cancel</a>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
}

    else{?>


<div class="note">
    <div class="note-header">
        <h3>Create a New Note</h3>
    </div>
    <div class="note-content">
        <form action="note.php" method="post">
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea class="form-control" name="content" id="content" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
<br>
<?php

$sql = "SELECT * FROM note WHERE username = '".$_SESSION['username']."' ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='note'>";
        echo "<div class='note-header'>";
        echo "<h3>#" . $row['id'] . "</h3>";
        echo "<p style='font-size: 14px; color: #555;'><i class='fa fa-calendar'></i> Created: " . $row['created_at'] . "</p>";
        echo "<p style='font-size: 14px; color: #555;'><i class='fa fa-calendar'></i> Edited: " . $row['modified_at'] . "</p>";
        echo "</div>";
        echo "<div class='note-content'>";
        echo nl2br($row['content']);
        echo "</div>";
        echo "<div class='note-footer'>";
        echo "<div style='display: flex; justify-content: center;'>";
        echo "<a href='note.php?delete=" . $row['id'] . "' class='btn btn-danger mr-2' style='margin-right: 10px;'><i class='fa fa-trash'></i> Delete</a>";
        echo "<a href='note.php?edit=" . $row['id'] . "' class='btn btn-warning' style='margin-left: 10px;'><i class='fa fa-edit'></i> Edit</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}
else{
    echo "<div class='note'>";
    echo "<div class='note-header'>";
    echo "<h3>No notes found</h3>";
    echo "</div>";
    echo "</div>";
}

?>

   <?php 
}
?>




<?php

if (isset($_POST['content'])) {
    $content = $_POST['content'];
    $username = $_SESSION['username'];
    if (!empty($content)) {
        $sql = "SELECT * FROM note WHERE username = '$username' ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['content'] != $content) {
                $sql = "INSERT INTO note (username, content) VALUES ('$username', '$content')";
                if ($conn->query($sql) === TRUE) {
echo "<script>window.location.href='note.php?msg=Success';</script>";

                } else {
                    echo "<script>window.location.href='note.php?msg=Error';</script>";
                }
            }
        } else {
            $sql = "INSERT INTO note (username, content) VALUES ('$username', '$content')";
            if ($conn->query($sql) === TRUE) {
              echo "<script>window.location.href='note.php?msg=Success';</script>";
            } else {
                echo "<script>window.location.href='note.php?msg=Error';</script>";
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM note WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
    echo "<script>window.location.href='note.php?msg=Success';</script>";
    } else {
        echo "<script>window.location.href='note.php?msg=Error';</script>";
        
    }
}



if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $content = $_POST['content'];
    $sql = "UPDATE note SET content = '$content' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>window.location.href='note.php?msg=Success';</script>";
    } else {
        echo "<script>window.location.href='note.php?msg=Error';</script>";
    }
}
?>
<?php

require_once 'footer.php';
?>