<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Conway Game of Life</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #9AD6AC;
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            padding: 20px;
        }

        .game-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            margin-top: 30px;
        }

        #grid {
            display: grid;
            grid-template-columns: repeat(20, 20px);
            grid-template-rows: repeat(20, 20px);
            gap: 2px;
            background-color: #444;
            padding: 10px;
        }

        .cell {
            width: 20px;
            height: 20px;
            background-color: black;
            cursor: pointer;
        }

        .alive {
            background-color: #5d8eeb;
        }

        .side-panel {
            display: inline-block;
            vertical-align: top;
            text-align: left;
        }

        .side-panel button {
            width: 100%;
            min-width: 160px;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .cycle-btn {
            background-color: #4CAF50;
            color: white;
        }

        .cycle-btn:hover {
            background-color: #45a049;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
        }

        .logout-btn:hover {
            background-color: #e53935;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
    </style>
</head>
<body>

<h1>Game of Life</h1>

<div class="game-container">
<p id="cycle-counter"><strong>Cycles:</strong> 0</p>

    <div id="grid"></div>

    <div class="side-panel">
	<button class="cycle-btn" onclick="startGame()">Start Game</button>
	<button class="cycle-btn" onclick="stopGame()">Stop Game</button>
        <button class="cycle-btn" onclick="nextGeneration(); updateCycles(1);">Next Generation</button>
	<button class="cycle-btn" onclick="nextGenerations(23); updateCycles(23);">Next 23 Generations</button>
        <button class="cycle-btn" onclick="resetGame()">Reset Game</button>
<select id="preset-grid" onchange="applyPresetGrid()">
    <option value="">Select Grid Pattern</option>
    <option value="box">The Box</option>
    <option value="beacon">The Beacon</option>
    <option value="glider">The Glider</option>
</select>


<div class="btn-container">
            <button class="logout-btn" onclick="endGameAndRedirect('player_dashboard.php')">Return to Dashboard</button>
            <button class="logout-btn" onclick="endGameAndRedirect('logout.php')">Logout</button>
        </div>
    </div>
</div>

<script>
let cycleCount = 0;

// Initialize grid and store cell states (alive/dead)
const grid = document.getElementById('grid');
const size = 20;
const cells = [];
for (let i = 0; i < size * size; i++) {
    const cell = document.createElement('div');
    cell.className = 'cell';
    cell.dataset.alive = '0';  // 0 = dead, 1 = alive
    cell.addEventListener('click', () => toggleCellState(cell, i));
    grid.appendChild(cell);
    cells.push(cell);
}

// Toggle cell state (alive/dead)
function toggleCellState(cell, index) {
    const isAlive = cell.classList.toggle('alive');
    cell.dataset.alive = isAlive ? '1' : '0';
}

// Get neighbors of a given cell (returns an array of surrounding cells)
function getNeighbors(index) {
    const neighbors = [];
    const row = Math.floor(index / size);
    const col = index % size;

    const positions = [
        [-1, -1], [-1, 0], [-1, 1],
        [0, -1],          [0, 1],
        [1, -1],  [1, 0], [1, 1]
    ];

    positions.forEach(([dx, dy]) => {
        // Wrap around using modulo
        const newRow = (row + dx + size) % size;
        const newCol = (col + dy + size) % size;
        neighbors.push(cells[newRow * size + newCol]);
    });

    return neighbors;
}


// Calculate the next generation based on Conway's Game of Life rules
function nextGeneration() {
    const newStates = cells.map(cell => {
        const isAlive = cell.dataset.alive === '1';
        const neighbors = getNeighbors(cells.indexOf(cell));

        const aliveNeighbors = neighbors.filter(neighbor => neighbor.dataset.alive === '1').length;

        if (isAlive) {
            // Rule 1: Any live cell with fewer than two live neighbors dies (underpopulation)
            // Rule 2: Any live cell with more than three live neighbors dies (overcrowding)
            if (aliveNeighbors < 2 || aliveNeighbors > 3) {
                return '0'; // cell dies
            } else {
                return '1'; // cell stays alive
            }
        } else {
            // Rule 4: Any dead cell with exactly three live neighbors becomes a live cell
            if (aliveNeighbors === 3) {
                return '1'; // cell becomes alive
            }
            return '0'; // cell stays dead
        }
    });

    // Update the grid with the new states
    cells.forEach((cell, i) => {
        const newState = newStates[i];
        cell.dataset.alive = newState;
        if (newState === '1') {
            cell.classList.add('alive');
        } else {
            cell.classList.remove('alive');
        }
    });
}

// Update the grid for 23 generations
function nextGenerations(generations) {
    let count = 0;
    const interval = setInterval(() => {
        nextGeneration();
        count++;
        if (count === generations) {
            clearInterval(interval);
        }
    }, 1000); 
}

// Start game timer and reset cycle count
fetch('start_game_session.php')
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert("Error starting game session.");
        } else {
            cycleCount = 0;
            document.getElementById('cycle-counter').textContent = "Cycles: 0";
        }
    });


// Stop game timer and redirect
function endGameAndRedirect(targetPage) {
    fetch('end_game_session.php')
        .then(() => {
            window.location.href = targetPage;
        });
}

function updateCycles(count) {
    cycleCount += count;
    document.getElementById('cycle-counter').textContent = "Cycles: " + cycleCount;

    fetch('update_cycles.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'increment=' + count
    })
    .then(res => res.json())
    .then(data => {
        console.log('Cycle update response:', data);
    })
    .catch(err => console.error('Cycle update error:', err));
}


let gameInterval = null; // holds interval ID

function startGame() {
    if (!gameInterval) {
        gameInterval = setInterval(() => {
            nextGeneration();
            updateCycles(1);
        }, 200); 
    }
}

function stopGame() {
    if (gameInterval) {
        clearInterval(gameInterval);
        gameInterval = null;
    }
}

function resetGame() {
    cells.forEach(cell => {
        cell.dataset.alive = '0';
        cell.classList.remove('alive');
    });

    cycleCount = 0;
    document.getElementById('cycle-counter').textContent = "Cycles: 0";
if (gameInterval) {
    clearInterval(gameInterval);
    gameInterval = null;
}

}

function applyPresetGrid() {
    const pattern = document.getElementById('preset-grid').value;

    // Reset all cells to dead
    cells.forEach(cell => {
        cell.dataset.alive = '0';
        cell.classList.remove('alive');
    });

   // Apply the selected pattern
    if (pattern === 'box') {
        setPattern([[9, 10], [10, 9], [10, 10], [9, 9]]);
    } else if (pattern === 'beacon') {
        setPattern([ 
            [8, 8], [8, 9], [9, 8],
            [10, 11], [11, 10], [11, 11], 
        ]);
    } else if (pattern === 'glider') {
        setPattern([[9, 8], [9, 10], [10, 10], [10, 9], [11, 9]]);
    }
}

// Function to set the pattern of live cells
function setPattern(coords) {
    coords.forEach(([row, col]) => {
        // Ensure row and col are within the grid size
        if (row >= 0 && row < size && col >= 0 && col < size) {
            const index = row * size + col;
            if (cells[index]) {
                cells[index].dataset.alive = '1';
                cells[index].classList.add('alive');
            }
        }
    });
}


</script>

</body>
</html>
