<?php
session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'player' or 'admin'

    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $successMessage = "Registration successful. <a href='login.php'>Login here</a>";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Register</h1>

    <?php
    if (!empty($successMessage)) {
        echo "<p style='color: green;'>$successMessage</p>";
    }
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }
    ?>

    <form method="post" action="">
        <input type="text" name="name" placeholder="Full Name"><br>
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <select name="role">
            <option value="player">Player</option>
            <option value="admin">Admin</option>
        </select><br>
        <input type="submit" value="Register">
    </form>

<p class="centered-text">Already have an account?</p>
<a href="login.php"><button type="button">Go to Login</button></a>

</div>
</body>
</html>
