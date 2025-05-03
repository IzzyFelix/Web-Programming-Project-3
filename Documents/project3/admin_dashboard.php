<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "ifelix2", "ifelix2", "ifelix2");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Total games played
$query = "SELECT SUM(games_played) AS total_games FROM users";
$result = mysqli_query($conn, $query);
$total_games = mysqli_fetch_assoc($result)['total_games'] ?? 0;

// Top 5 users with most games played
$query = "SELECT name, games_played FROM users ORDER BY games_played DESC LIMIT 5";
$result = mysqli_query($conn, $query);
$top_users = [];
$top_games = [];
while ($row = mysqli_fetch_assoc($result)) {
    $top_users[] = $row['name'];
    $top_games[] = $row['games_played'];
}

// All users and their cycles
$query = "SELECT name, cycles FROM users ORDER BY name ASC";
$result = mysqli_query($conn, $query);
$cycle_users = [];
$cycle_counts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cycle_users[] = $row['name'];
    $cycle_counts[] = $row['cycles'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #9AD6AC;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h1 {
            padding: 20px;
            background-color: #4CAF50;
            color: white;
            margin: 0;
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .side-panel {
            width: 200px;
            text-align: left;
        }

        .side-panel button {
            width: 100%;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .admin-actions {
            background-color: #f44336;
            color: white;
        }

        .admin-actions:hover {
            background-color: #e53935;
        }

        .content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 80%;
        }

        .box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            text-align: left;
        }

        .analytics-container {
            margin-top: 20px;
        }

        canvas {
            max-width: 100%;
            margin-top: 30px;
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
	margin-bottom: 15px;
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
	margin-bottom: 15px;
	min-width: 200px;
        }

        .logout-btn:hover {
            background-color: #e53935;
        }
    </style>
    <script src="chart.umd.js"></script>
</head>
<body>

<h1>Welcome Admin!</h1>

<div class="dashboard-container">
    <div class="side-panel">

<div class ="box" style="text-align: center;">
  <a href="game.php" class="start-btn">Start Game</a>

<a href="logout.php" class="logout-btn">Logout</a>
</div>


       <!-- Suspend/Unsuspend User Dropdown -->
<div class="box">
    <form method="POST" action="suspend_user.php">
        <label for="user_action">Manage user status:</label>
        <select name="user_id" id="user_action">
            <?php
            $user_query = $conn->query("SELECT id, name, suspended FROM users WHERE role != 'admin'");
            while ($row = $user_query->fetch_assoc()) {
                $status = ($row['suspended'] === 'yes') ? ' (Suspended)' : '';
                echo "<option value='{$row['id']}'>{$row['name']}{$status}</option>";
            }
            ?>
        </select>
        <select name="action">
            <option value="suspend">Suspend</option>
            <option value="unsuspend">Unsuspend</option>
        </select>
        <button type="submit" class="admin-actions">Apply</button>
    </form>
</div>


    </div>

    <div class="content">
        <!-- Top 5 Users Box -->
        <div class="box">
            <h3>Top 5 Users with Most Games Played:</h3>
            <ul>
                <?php foreach ($top_users as $index => $user): ?>
                    <li><?php echo $user; ?> - <?php echo $top_games[$index]; ?> games played</li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Total Games Played Box -->
        <div class="box">
            <h3>Total Games Played by All Users:</h3>
            <h4><?php echo $total_games; ?> games played</h4>
        </div>

        <!-- Analytics Charts -->
        <div class="analytics-container">
            <h3>Analytics:</h3>
            <canvas id="topUsersChart"></canvas>
            <canvas id="cyclesChart"></canvas>
        </div>
    </div>
</div>



<script>
// PHP data to JS
const topUsers = <?php echo json_encode($top_users); ?>;
const topGames = <?php echo json_encode($top_games); ?>;
const cycleUsers = <?php echo json_encode($cycle_users); ?>;
const cycleCounts = <?php echo json_encode($cycle_counts); ?>;

// Chart for Top 5 Users
const topUsersCtx = document.getElementById('topUsersChart').getContext('2d');
const topUsersChart = new Chart(topUsersCtx, {
    type: 'bar',
    data: {
        labels: topUsers,
        datasets: [{
            label: 'Games Played',
            data: topGames,
            backgroundColor: '#5d8eeb',
            borderColor: '#4e7db2',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Chart for Cycles per User
const cyclesCtx = document.getElementById('cyclesChart').getContext('2d');
const cyclesChart = new Chart(cyclesCtx, {
    type: 'bar',
    data: {
        labels: cycleUsers,
        datasets: [{
            label: 'Cycles Completed',
            data: cycleCounts,
            backgroundColor: '#4caf50',
            borderColor: '#388e3c',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

</body>
</html>