<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$increment = intval($_POST['increment'] ?? 1);

// Update the 'cycles' column for the user
$stmt = $conn->prepare("UPDATE users SET cycles = cycles + ? WHERE id = ?");
$stmt->bind_param("ii", $increment, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
