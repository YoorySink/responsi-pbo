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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Training Academy - History</title>
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
            color: #60a5fa;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }
        
        .history-item {
            padding: 1rem;
            border-radius: 0.5rem;
            background: #111827;
            border: 1px solid #1f2937;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #1f2937;
        }
        
        .history-pokemon {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .history-time {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .history-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .history-detail-box {
            background: rgba(0, 0, 0, 0.3);
            padding: 0.5rem;
            border-radius: 0.375rem;
        }
        
        .history-detail-label {
            font-size: 0.625rem;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        
        .history-detail-value {
            font-size: 0.875rem;
            color: #f3f4f6;
        }
        
        .history-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .history-stat-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 0.5rem;
            border-radius: 0.375rem;
        }
        
        .history-stat-label {
            font-size: 0.625rem;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .history-stat-change {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .history-stat-change .before {
            color: #ef4444;
        }
        
        .history-stat-change .arrow {
            color: #9ca3af;
            margin: 0 0.25rem;
        }
        
        .history-stat-change .after {
            color: #22c55e;
            font-weight: 600;
        }
        
        .history-unlocked {
            font-size: 0.75rem;
            color: #86efac;
            background: rgba(34, 197, 94, 0.1);
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        
        .history-empty {
            color: #6b7280;
            text-align: center;
            padding: 2rem 0;
        }
        
        .max-h-96 {
            max-height: 48rem;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pokémon Training Academy</h1>

        <!-- Navigation -->
        <div class="nav-buttons">
            <a href="index.php" class="nav-btn">🏠 Beranda</a>
            <a href="training.php" class="nav-btn">💪 Latihan</a>
            <a href="history.php" class="nav-btn active">📜 Riwayat</a>
        </div>

        <!-- History -->
        <div class="card">
            <h2>Training History</h2>
            <div class="max-h-96">
                <?php if (empty($history)): ?>
                <div class="history-empty">Belum ada riwayat latihan</div>
                <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($history as $historyItem): ?>
                    <div class="history-item">
                        <div class="history-header">
                            <span class="history-pokemon"><?= htmlspecialchars($historyItem['pokemon']) ?></span>
                            <span class="history-time">⏰ <?= htmlspecialchars($historyItem['time']) ?></span>
                        </div>
                        
                        <?php if (isset($historyItem['before']) && isset($historyItem['after'])): ?>
                            <!-- Detail latihan dengan before/after -->
                            <div class="history-details">
                                <?php
                                // Parse text untuk ambil jenis, intensitas, durasi
                                preg_match('/^(.+?) \((.+?)\) (\d+)min$/', $historyItem['text'], $matches);
                                $jenis = $matches[1] ?? 'Unknown';
                                $intensitas = $matches[2] ?? 'Unknown';
                                $durasi = $matches[3] ?? '0';
                                ?>
                                <div class="history-detail-box">
                                    <div class="history-detail-label">Jenis Latihan</div>
                                    <div class="history-detail-value">💪 <?= htmlspecialchars($jenis) ?></div>
                                </div>
                                <div class="history-detail-box">
                                    <div class="history-detail-label">Intensitas</div>
                                    <div class="history-detail-value">⚡ <?= htmlspecialchars($intensitas) ?></div>
                                </div>
                                <div class="history-detail-box">
                                    <div class="history-detail-label">Durasi</div>
                                    <div class="history-detail-value">⏱️ <?= htmlspecialchars($durasi) ?> menit</div>
                                </div>
                                <div class="history-detail-box">
                                    <div class="history-detail-label">Energy Digunakan</div>
                                    <div class="history-detail-value">🔋 -<?= htmlspecialchars($durasi) ?></div>
                                </div>
                            </div>
                            
                            <div class="history-stats">
                                <div class="history-stat-item">
                                    <div class="history-stat-label">Level</div>
                                    <div class="history-stat-change">
                                        <span class="before"><?= $historyItem['before']['level'] ?></span>
                                        <span class="arrow">→</span>
                                        <span class="after"><?= $historyItem['after']['level'] ?></span>
                                    </div>
                                </div>
                                <div class="history-stat-item">
                                    <div class="history-stat-label">HP</div>
                                    <div class="history-stat-change">
                                        <span class="before"><?= $historyItem['before']['hp'] ?></span>
                                        <span class="arrow">→</span>
                                        <span class="after"><?= $historyItem['after']['hp'] ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (isset($historyItem['unlocked']) && !empty($historyItem['unlocked'])): ?>
                            <div class="history-unlocked">
                                ✨ <strong>New Moves Unlocked:</strong> <?= htmlspecialchars(implode(', ', $historyItem['unlocked'])) ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Item history lainnya (seperti Rest) -->
                            <div class="history-detail-box">
                                <div class="history-detail-value"><?= htmlspecialchars($historyItem['text']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
