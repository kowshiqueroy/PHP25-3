<?php session_start(); if (!$_SESSION['admin']) die("Access Denied"); ?>
<h2>Admin Dashboard</h2>
<a href="manage_lessons.php">Manage Lessons</a><br>
<a href="manage_quizzes.php">Manage Quizzes</a><br>
<a href="manage_problems.php">Manage Problems</a><br>
<a href="login.php">Logout</a>