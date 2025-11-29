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
                    
                    // history dan save ke JSON
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
    <title>Pokémon Training Academy - Training</title>
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
        
        label {
            display: block;
            font-size: 0.875rem;
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }
        
        .current-pokemon {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid #8b5cf6;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .current-pokemon-name {
            font-weight: bold;
            font-size: 1.125rem;
        }
        
        .current-pokemon-info {
            font-size: 0.875rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pokémon Training Academy</h1>

        <!-- Navigation -->
        <div class="nav-buttons">
            <a href="index.php" class="nav-btn">🏠 Beranda</a>
            <a href="training.php" class="nav-btn active">💪 Latihan</a>
            <a href="history.php" class="nav-btn">📜 Riwayat</a>
        </div>

        <?php if ($alert): ?>
        <div class="alert <?= $alertType ?>">
            <?= htmlspecialchars($alert) ?>
        </div>
        <?php endif; ?>

        <!-- Current Pokemon Info -->
        <div class="current-pokemon">
            <div class="current-pokemon-name">
                <?= htmlspecialchars($currentPokemon->getName()) ?> - Level <?= $currentPokemon->getLevel() ?>
            </div>
            <div class="current-pokemon-info">
                Energy: <?= $currentPokemon->getEnergy() ?> | HP: <?= $currentPokemon->getHP() ?> | ATK: <?= $currentPokemon->getAtk() ?> | DEF: <?= $currentPokemon->getDef() ?> | SPD: <?= $currentPokemon->getSpd() ?>
            </div>
        </div>

        <!-- Training Center -->
        <div class="card">
            <h2>Training Center</h2>
            
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
    </div>
</body>
</html>
