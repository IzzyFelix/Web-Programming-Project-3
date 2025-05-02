<?php
session_start();

// Redirect if not logged in or not a player
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the username and games played of the logged-in user
$sql = "SELECT name, games_played FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($username, $gamesPlayed);
$stmt->fetch();
$stmt->close();

// Query to get the total time played (duration in seconds)
$sql = "SELECT SUM(duration) AS total_time FROM game_sessions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($totalTime);
$stmt->fetch();
$stmt->close();

// Query to get the total number of generations run
$sql = "SELECT cycles FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($totalCycles);
$stmt->fetch();
$stmt->close();


// Format the time to HH:MM:SS
$formattedTime = $totalTime ? gmdate("H:i:s", $totalTime) : "00:00:00";

// stats display
$stats = [
    'Games Played' => $gamesPlayed,
    'Total Time Played' => $formattedTime,  
    'Total Generations' => $totalCycles
];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .dashboard img {
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .dashboard h2 {
            margin: 10px 0;
            color: #333;
        }

        .stats {
            text-align: left;
            margin-top: 20px;
        }

        .stats p {
            margin: 8px 0;
        }

        .start-btn {
            margin-top: 30px;
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .start-btn:hover {
            background-color: #45a049;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <img src="profile.jpg" alt="Avatar" width="100" height="100">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

    <div class="stats">
        <?php foreach ($stats as $label => $value): ?>
            <p><strong><?php echo $label; ?>:</strong> <?php echo $value; ?></p>
        <?php endforeach; ?>
    </div>

    <a href="game.php" class="start-btn">Start Game</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
