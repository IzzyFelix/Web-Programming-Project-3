<?php
session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, password, role, suspended FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId, $hashedPassword, $role, $suspended);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            if ($suspended === 'yes') {
                $errors[] = "Your account has been suspended. Please contact the administrator.";
            } else {
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: player_dashboard.php");
                }
                exit();
            }
        } else {
            $errors[] = "Invalid password.";
        }
    } else {
        $errors[] = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<div class="container">
    <h1>Login</h1>

    <?php
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>

    <p class="centered-text">Don't have an account?</p>
    <a href="register.php"><button type="button">Register Here</button></a>
</div>
</body>
</html>
