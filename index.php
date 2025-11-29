<?php
require_once "autoload.php";

// Helper functions for JSON history
function loadHistory() {
    $file = 'history.json';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function saveHistory($history) {
    $file = 'history.json';
    file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

//start session setelah autoload aktip
session_start();

// Initialize session data
if (!isset($_SESSION['pokemon'])) {
    $_SESSION['pokemon'] = [
        new ElectricPokemon("Raichu"),
        new GrassPokemon("Bulbasaur"),
        new FirePokemon("Charmander"),
        new WaterPokemon("Squirtle")
    ];
    $_SESSION['selected'] = 0;
}

if (!isset($_SESSION['selected'])) {
    $_SESSION['selected'] = 0;
}

// Load history from JSON file
$history = loadHistory();

// Handle POST actions
$alert = null;
$alertType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'select_pokemon':
            $_SESSION['selected'] = (int)$_POST['pokemon_index'];
            unset($_SESSION['category']);
            unset($_SESSION['choices']);
            unset($_SESSION['choice_index']);
            unset($_SESSION['duration']);
            break;
            
        case 'rest':
            $currentPokemon = $_SESSION['pokemon'][$_SESSION['selected']];
            $beforeEnergy = $currentPokemon->getEnergy();
            $currentPokemon->rest();
            
            array_unshift($history, [
                'time' => date('Y-m-d H:i:s'),
                'pokemon' => $currentPokemon->getName(),
                'text' => sprintf("Rest: Energy %d→%d", $beforeEnergy, $currentPokemon->getEnergy())
            ]);
            saveHistory($history);
            
            $alertType = 'success';
            $alert = sprintf("Energy restored: %d→%d", $beforeEnergy, $currentPokemon->getEnergy());
            break;
            
        case 'reset':
            // Reset semua Pokemon ke kondisi awal
            $_SESSION['pokemon'] = [
                new ElectricPokemon("Raichu"),
                new GrassPokemon("Bulbasaur"),
                new FirePokemon("Charmander"),
                new WaterPokemon("Squirtle")
            ];
            $_SESSION['selected'] = 0;
            unset($_SESSION['category']);
            unset($_SESSION['choices']);
            unset($_SESSION['choice_index']);
            unset($_SESSION['duration']);
            
            $alertType = 'success';
            $alert = "Semua Pokemon telah direset ke kondisi awal!";
            break;
    }
}

$currentPokemon = $_SESSION['pokemon'][$_SESSION['selected']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Training Academy - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0a0a0a;
            color: #f3f4f6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 1.5rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 2.25rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(to right, #c084fc, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .nav-btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: rgba(30, 30, 30, 0.9);
            color: #f3f4f6;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .nav-btn:hover {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.2);
        }
        
        .nav-btn.active {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
        }
        
        .alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        
        .alert.success {
            background: #14532d;
            color: #bbf7d0;
        }
        
        .alert.error {
            background: #7f1d1d;
            color: #fecaca;
        }
        
        .card {
            background: rgba(30, 30, 30, 0.9);
            border: 1px solid #333;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .card h2 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #c084fc;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }
        
        .space-y-3 > * + * {
            margin-top: 0.75rem;
        }
        
        button, input[type="submit"] {
            cursor: pointer;
            border: none;
            font: inherit;
            transition: all 0.2s;
        }
        
        button:disabled, input[type="submit"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-pokemon {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: rgba(30, 30, 30, 0.9);
            color: #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-pokemon:hover:not(.selected) {
            border-color: #4b5563;
        }
        
        .btn-pokemon.selected {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
        }
        
        .pokemon-level {
            font-size: 0.875rem;
            color: #9ca3af;
        }
        
        .stat-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stat-label {
            color: #9ca3af;
        }
        
        .stat-value {
            font-weight: bold;
        }
        
        .energy-bar {
            height: 0.5rem;
            background: #374151;
            border-radius: 9999px;
            margin-top: 0.25rem;
            overflow: hidden;
        }
        
        .energy-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s;
        }
        
        .energy-fill.high { background: #22c55e; }
        .energy-fill.medium { background: #eab308; }
        .energy-fill.low { background: #ef4444; }
        
        .moves-container {
            margin-top: 1rem;
        }
        
        .moves-label {
            font-size: 0.875rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }
        
        .moves-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        
        .move-badge {
            padding: 0.25rem 0.5rem;
            background: #581c87;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }
        
        .btn-rest {
            width: 100%;
            padding: 0.5rem;
            background: #16a34a;
            color: white;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-rest:hover:not(:disabled) {
            background: #15803d;
        }
        
        .btn-rest:disabled {
            background: #374151;
        }
        
        .btn-reset {
            width: 100%;
            padding: 0.5rem;
            background: #dc2626;
            color: white;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .btn-reset:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pokémon Training Academy</h1>

        <!-- Navigation -->
        <div class="nav-buttons">
            <a href="index.php" class="nav-btn active">🏠 Beranda</a>
            <a href="training.php" class="nav-btn">💪 Latihan</a>
            <a href="history.php" class="nav-btn">📜 Riwayat</a>
        </div>

        <?php if ($alert): ?>
        <div class="alert <?= $alertType ?>">
            <?= htmlspecialchars($alert) ?>
        </div>
        <?php endif; ?>

        <!-- Pokemon Selection -->
        <div class="card">
            <h2>Pilih Pokémon</h2>
            <div class="space-y-2">
                <?php foreach ($_SESSION['pokemon'] as $index => $pokemon): ?>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="select_pokemon">
                    <input type="hidden" name="pokemon_index" value="<?= $index ?>">
                    <button type="submit" class="btn-pokemon <?= $index === $_SESSION['selected'] ? 'selected' : '' ?>">
                        <span><?= htmlspecialchars($pokemon->getName()) ?> (<?= htmlspecialchars($pokemon->getType()) ?>)</span>
                        <span class="pokemon-level">Lv.<?= $pokemon->getLevel() ?></span>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="card">
            <h2><?= htmlspecialchars($currentPokemon->getName()) ?> - Level <?= $currentPokemon->getLevel() ?></h2>
            
            <div class="space-y-3" style="margin-bottom: 1rem;">
                <div class="stat-row">
                    <span class="stat-label">HP:</span>
                    <span class="stat-value"><?= $currentPokemon->getHP() ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">ATK:</span>
                    <span class="stat-value"><?= $currentPokemon->getAtk() ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">DEF:</span>
                    <span class="stat-value"><?= $currentPokemon->getDef() ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">SPD:</span>
                    <span class="stat-value"><?= $currentPokemon->getSpd() ?></span>
                </div>
                <div>
                    <div class="stat-row">
                        <span class="stat-label">Energy:</span>
                        <span class="stat-value"><?= $currentPokemon->getEnergy() ?></span>
                    </div>
                    <div class="energy-bar">
                        <?php
                        $energy = $currentPokemon->getEnergy();
                        $energyClass = $energy > 60 ? 'high' : ($energy > 30 ? 'medium' : 'low');
                        ?>
                        <div class="energy-fill <?= $energyClass ?>" style="width: <?= $energy ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="moves-container">
                <div class="moves-label">Moves:</div>
                <div class="moves-list">
                    <?php foreach ($currentPokemon->getMoves() as $move): ?>
                    <span class="move-badge"><?= htmlspecialchars($move) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="rest">
                <button type="submit" class="btn-rest" <?= $currentPokemon->getEnergy() >= 100 ? 'disabled' : '' ?>>
                    😴 Rest (+20 Energy)
                </button>
            </form>
            
            <form method="POST">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="btn-reset">
                    🔄 Reset Semua Pokemon
                </button>
            </form>
        </div>
    </div>
</body>
</html>
