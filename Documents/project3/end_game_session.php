<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit();
}

$conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
if ($conn->connect_error) {
    exit();
}

$user_id = $_SESSION['user_id'];
$conn->query("
    UPDATE game_sessions
    SET end_time = NOW(), duration = TIMESTAMPDIFF(SECOND, start_time, NOW())
    WHERE user_id = $user_id AND end_time IS NULL
");

$conn->close();
?>
