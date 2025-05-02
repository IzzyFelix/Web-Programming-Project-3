<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
if ($conn->connect_error) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Increment games_played when starting a new game session
$conn->query("UPDATE users SET games_played = games_played + 1 WHERE id = $user_id");

$conn->query("INSERT INTO game_sessions (user_id, start_time) VALUES ($user_id, NOW())");

echo json_encode(['success' => true]);
$conn->close();
?>
