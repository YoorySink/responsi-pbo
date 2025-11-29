<?php
// Include all required classes BEFORE session_start()
// This ensures classes are loaded before unserializing session objects
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

// Now start session after all classes are loaded
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
            
        case 'select_category':
            $_SESSION['category'] = $_POST['category'];
            unset($_SESSION['choices']);
            unset($_SESSION['choice_index']);
            unset($_SESSION['duration']);
            break;
            
        case 'generate_choices':
            $currentPokemon = $_SESSION['pokemon'][$_SESSION['selected']];
            $_SESSION['choices'] = Training::generateChoices($currentPokemon->getType(), $_SESSION['category']);
            unset($_SESSION['choice_index']);
            unset($_SESSION['duration']);
            break;
            
        case 'select_choice':
            $_SESSION['choice_index'] = (int)$_POST['choice_index'];
            break;
            
        case 'select_duration':
            $_SESSION['duration'] = (int)$_POST['duration'];
            break;
            
        case 'train':
            if (isset($_SESSION['choice_index']) && isset($_SESSION['duration'])) {
                $currentPokemon = $_SESSION['pokemon'][$_SESSION['selected']];
                $selectedChoice = $_SESSION['choices'][$_SESSION['choice_index']];
                
                $result = Training::process(
                    $currentPokemon,
                    $selectedChoice['type'],
                    $_SESSION['category'],
                    $_SESSION['duration']
                );
                
                if ($result['success']) {
                    $alertType = 'success';
                    $alert = sprintf(
                        "Level %d→%d | HP %d→%d | Energy %d→%d",
                        $result['before']['level'],
                        $result['after']['level'],
                        $result['before']['hp'],
                        $result['after']['hp'],
                        $result['before']['energy'],
                        $result['after']['energy']
                    );
                    
                    if (!empty($result['unlockedMoves'])) {
                        $alert .= " | Unlocked: " . implode(', ', $result['unlockedMoves']);
                    }
                    
                    // Add to history and save to JSON
                    array_unshift($history, [
                        'time' => date('Y-m-d H:i:s'),
                        'pokemon' => $currentPokemon->getName(),
                        'text' => sprintf(
                            "%s (%s) %dmin",
                            $_SESSION['category'],
                            $selectedChoice['type'],
                            $_SESSION['duration']
                        ),
                        'before' => $result['before'],
                        'after' => $result['after'],
                        'unlocked' => $result['unlockedMoves']
                    ]);
                    saveHistory($history);
                } else {
                    $alertType = 'error';
                    $alert = $result['message'];
                }
                
                unset($_SESSION['choices']);
                unset($_SESSION['choice_index']);
                unset($_SESSION['duration']);
            }
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
            break;
    }
}

$currentPokemon = $_SESSION['pokemon'][$_SESSION['selected']];
$selectedCategory = $_SESSION['category'] ?? null;
$choices = $_SESSION['choices'] ?? null;
$selectedChoiceIndex = $_SESSION['choice_index'] ?? null;
$selectedDuration = $_SESSION['duration'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Training Academy</title>
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
            max-width: 1200px;
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
        
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
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
        }
        
        .card h2.purple { color: #c084fc; }
        .card h2.blue { color: #60a5fa; }
        
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
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .btn-category {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: rgba(30, 30, 30, 0.9);
            color: #f3f4f6;
        }
        
        .btn-category:hover:not(.selected) {
            border-color: #4b5563;
        }
        
        .btn-category.selected {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
        }
        
        .btn-generate {
            width: 100%;
            padding: 0.5rem;
            background: #4f46e5;
            color: white;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .btn-generate:hover {
            background: #4338ca;
        }
        
        .btn-choice {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: rgba(30, 30, 30, 0.9);
            color: #f3f4f6;
            text-align: left;
        }
        
        .btn-choice:hover:not(.selected) {
            border-color: #4b5563;
        }
        
        .btn-choice.selected {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
        }
        
        .choice-text {
            font-size: 0.875rem;
        }
        
        .choice-info {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }
        
        .choice-bonus {
            color: #86efac;
        }
        
        .duration-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .btn-duration {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: rgba(30, 30, 30, 0.9);
            color: #f3f4f6;
            text-align: center;
        }
        
        .btn-duration:hover:not(.selected) {
            border-color: #4b5563;
        }
        
        .btn-duration.selected {
            background: rgba(139, 92, 246, 0.2);
            border-color: #8b5cf6;
        }
        
        .duration-cost {
            font-size: 0.75rem;
            color: #9ca3af;
            display: block;
        }
        
        .btn-train {
            width: 100%;
            padding: 0.75rem;
            background: #ea580c;
            color: white;
            border-radius: 0.5rem;
            font-weight: bold;
        }
        
        .btn-train:hover:not(:disabled) {
            background: #c2410c;
        }
        
        .btn-train:disabled {
            background: #374151;
        }
        
        .history-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: #111827;
            border: 1px solid #1f2937;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }
        
        .history-pokemon {
            font-weight: 600;
        }
        
        .history-time {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .history-text {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .history-unlocked {
            font-size: 0.75rem;
            color: #86efac;
            margin-top: 0.25rem;
        }
        
        .history-empty {
            color: #6b7280;
            text-align: center;
            padding: 2rem 0;
        }
        
        .max-h-96 {
            max-height: 24rem;
            overflow-y: auto;
        }
        
        label {
            display: block;
            font-size: 0.875rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pokémon Training Academy</h1>

        <?php if ($alert): ?>
        <div class="alert <?= $alertType ?>">
            <?= htmlspecialchars($alert) ?>
        </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Left Column: Pokemon & Stats -->
            <div>
                <!-- Pokemon Selection -->
                <div class="card">
                    <h2 class="purple">Select Pokémon</h2>
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
                    <h3 style="font-size: 1.125rem; font-weight: bold; margin-bottom: 1rem;">
                        <?= htmlspecialchars($currentPokemon->getName()) ?> - Level <?= $currentPokemon->getLevel() ?>
                    </h3>
                    
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
                </div>
            </div>

            <!-- Right Column: Training & History -->
            <div>
                <!-- Training Center -->
                <div class="card">
                    <h2 class="purple">Training Center</h2>
                    
                    <!-- Category Selection -->
                    <label>Category</label>
                    <div class="category-grid">
                        <?php foreach (['Attack', 'Defense', 'Speed'] as $category): ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="select_category">
                            <input type="hidden" name="category" value="<?= $category ?>">
                            <button type="submit" class="btn-category <?= $selectedCategory === $category ? 'selected' : '' ?>">
                                <?= $category ?>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>

                    <!-- Generate Button -->
                    <?php if ($selectedCategory && !$choices): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="generate_choices">
                        <button type="submit" class="btn-generate">
                            🎲 Generate Options
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Training Choices -->
                    <?php if ($choices): ?>
                    <label>Select Training</label>
                    <div class="space-y-2" style="margin-bottom: 1rem;">
                        <?php foreach ($choices as $index => $choice): ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="select_choice">
                            <input type="hidden" name="choice_index" value="<?= $index ?>">
                            <button type="submit" class="btn-choice <?= $selectedChoiceIndex === $index ? 'selected' : '' ?>">
                                <div class="choice-text"><?= htmlspecialchars($choice['text']) ?></div>
                                <div class="choice-info">
                                    <?= htmlspecialchars($choice['type']) ?>
                                    <?php if ($choice['type'] === $currentPokemon->getType()): ?>
                                    ⚡ <span class="choice-bonus">Bonus +1-2 Level</span>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>

                    <!-- Duration Selection -->
                    <label>Duration</label>
                    <div class="duration-grid">
                        <?php foreach ([10, 20, 30] as $duration): ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="select_duration">
                            <input type="hidden" name="duration" value="<?= $duration ?>">
                            <button type="submit" class="btn-duration <?= $selectedDuration === $duration ? 'selected' : '' ?>">
                                <?= $duration ?>min
                                <span class="duration-cost">-<?= $duration ?> energy</span>
                            </button>
                        </form>
                        <?php endforeach; ?>
                    </div>

                    <!-- Train Button -->
                    <form method="POST">
                        <input type="hidden" name="action" value="train">
                        <button type="submit" class="btn-train" <?= ($selectedChoiceIndex === null || $selectedDuration === null) ? 'disabled' : '' ?>>
                            💪 Start Training
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <!-- History -->
                <div class="card">
                    <h2 class="blue">History</h2>
                    <div class="max-h-96">
                        <?php if (empty($history)): ?>
                        <div class="history-empty">No history</div>
                        <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach (array_slice($history, 0, 10) as $historyItem): ?>
                            <div class="history-item">
                                <div class="history-header">
                                    <span class="history-pokemon"><?= htmlspecialchars($historyItem['pokemon']) ?></span>
                                    <span class="history-time"><?= htmlspecialchars($historyItem['time']) ?></span>
                                </div>
                                <div class="history-text"><?= htmlspecialchars($historyItem['text']) ?></div>
                                <?php if (isset($historyItem['unlocked']) && !empty($historyItem['unlocked'])): ?>
                                <div class="history-unlocked">✨ <?= htmlspecialchars(implode(', ', $historyItem['unlocked'])) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
