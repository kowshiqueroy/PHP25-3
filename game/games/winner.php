<?php
session_start();
include('../includes/db.php');
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");
$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

$winner_id = $conn->query("SELECT winner FROM luck WHERE id = $id")->fetch_assoc()['winner'] ?? '';

if ($winner_id !== 0) {
    if ($winner_id == $user_id) {
        echo " 🎉You Win!";
    } else {
        try {
            $winner_name = $conn->query("SELECT username FROM players WHERE id = $winner_id")->fetch_assoc()['username'] ?? '';
        } catch (Exception $e) {
            $winner_name = '';
        }
        if ($winner_name) {
            echo " 🎉" . $winner_name;
        }
    }
}

?>

