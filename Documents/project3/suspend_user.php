<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    $conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($action === 'suspend') {
        $stmt = $conn->prepare("UPDATE users SET suspended = 'yes' WHERE id = ?");
    } elseif ($action === 'unsuspend') {
        $stmt = $conn->prepare("UPDATE users SET suspended = 'no' WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
}

header("Location: admin_dashboard.php");
exit();
?>
