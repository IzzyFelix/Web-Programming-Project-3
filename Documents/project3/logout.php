<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-container {
            width: 80%;
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            text-align: center;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logout-container h2 {
            color: #333;
        }

        .logout-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-container a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h2>You have been logged out.</h2>
        <a href="login.php">Login Again</a>
    </div>
</body>
</html>
