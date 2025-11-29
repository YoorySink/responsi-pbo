<?php
session_start();
require_once "autoload.php";

/* ==========================================================
   INIT HISTORY & SESSION
   ========================================================== */
if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$sessionKey = 'pokemon_objects';

/* ==========================================================
   LOAD POKEMON FROM SESSION / CREATE DEFAULT
   ========================================================== */
if (!empty($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
    $pokemonList = [];
    foreach ($_SESSION[$sessionKey] as $id => $serialized) {
        $obj = @unserialize($serialized);
        if ($obj !== false) {
            $pokemonList[$id] = $obj;
        } else {
            // fallback create default objects (safety)
            if ($id == 1) $pokemonList[1] = new ElectricPokemon("Raichu");
            if ($id == 2) $pokemonList[2] = new GrassPokemon("Bulbasaur");
            if ($id == 3) $pokemonList[3] = new FirePokemon("Charmander");
            if ($id == 4) $pokemonList[4] = new WaterPokemon("Squirtle");
        }
    }
} else {
    // first time init and persist
    $pokemonList = [
        1 => new ElectricPokemon("Raichu"),
        2 => new GrassPokemon("Bulbasaur"),
        3 => new FirePokemon("Charmander"),
        4 => new WaterPokemon("Squirtle"),
    ];
    $_SESSION[$sessionKey] = [];
    foreach ($pokemonList as $id => $p) {
        $_SESSION[$sessionKey][$id] = serialize($p);
    }
}

/* ==========================================================
   DEFAULT UI / STATE VARS
   ========================================================== */
$categories = ["Attack", "Defense", "Speed"];
$error = null;
$generatedChoices = null;
$result = null;
$selectedPokemonId = (int)($_POST['pokemon'] ?? $_SESSION['last_pokemon'] ?? 1);
$selectedCategory = $_SESSION['last_category'] ?? null;

/* ==========================================================
   POST HANDLER
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? "form";

    /* --------------------------
       RESET
       -------------------------- */
    if ($action === 'reset') {
        unset($_SESSION['history']);
        unset($_SESSION['last_choices']);
        unset($_SESSION['last_pokemon']);
        unset($_SESSION['last_category']);
        unset($_SESSION[$sessionKey]);

        // recreate defaults in memory for immediate UI
        $pokemonList = [
            1 => new ElectricPokemon("Raichu"),
            2 => new GrassPokemon("Bulbasaur"),
            3 => new FirePokemon("Charmander"),
            4 => new WaterPokemon("Squirtle"),
        ];
        $_SESSION[$sessionKey] = [];
        foreach ($pokemonList as $id => $p) {
            $_SESSION[$sessionKey][$id] = serialize($p);
        }

        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* --------------------------
       GENERATE CHOICES
       -------------------------- */
    if ($action === 'generate') {
        $selectedPokemonId = (int)($_POST['pokemon'] ?? 0);
        $selectedCategory = $_POST['category'] ?? null;

        if (!isset($pokemonList[$selectedPokemonId]) || !in_array($selectedCategory, $categories)) {
            $error = "Pilihan tidak valid.";
        } else {
            $pokemon = $pokemonList[$selectedPokemonId];
            $generatedChoices = Training::generateChoices($pokemon->getType(), $selectedCategory);

            $_SESSION['last_choices'] = $generatedChoices;
            $_SESSION['last_pokemon'] = $selectedPokemonId;
            $_SESSION['last_category'] = $selectedCategory;
        }
    }

    /* --------------------------
       TRAIN
       -------------------------- */
    if ($action === 'train') {
        $selectedPokemonId = (int)($_POST['pokemon'] ?? 0);
        $choiceIndex = (int)($_POST['choice'] ?? -1);
        $durChoice = (int)($_POST['duration'] ?? 0);

        $durations = [ 1 => 10, 2 => 20, 3 => 30 ];

        if (!isset($pokemonList[$selectedPokemonId])) {
            $error = "Pokémon tidak ditemukan.";
        } elseif (!isset($_SESSION['last_choices']) || !isset($_SESSION['last_pokemon'])) {
            $error = "Silakan pilih latihan dulu.";
        } elseif ($_SESSION['last_pokemon'] !== $selectedPokemonId) {
            $error = "Pilihan latihan tidak cocok dengan Pokémon.";
        } elseif (!isset($_SESSION['last_choices'][$choiceIndex])) {
            $error = "Pilihan latihan tidak valid.";
        } elseif (!isset($durations[$durChoice])) {
            $error = "Durasi tidak valid.";
        } else {
            $pokemon = $pokemonList[$selectedPokemonId];
            $choice = $_SESSION['last_choices'][$choiceIndex];
            $duration = $durations[$durChoice];

            // Call Training::process which updates $pokemon object (mutates)
            $result = Training::process(
                $pokemon,
                $choice['type'],
                $_SESSION['last_category'],
                $duration
            );

            if (!$result['success']) {
                $error = $result['message'] ?? "Latihan gagal.";
                // still save pokemon object (no change) to be safe
                $_SESSION[$sessionKey][$selectedPokemonId] = serialize($pokemon);
            } else {
                // save history (ensure before/after arrays exist)
                $_SESSION['history'][] = [
                    "time"     => date("Y-m-d H:i:s"),
                    "type"     => "train",
                    "pokemon"  => $pokemon->getName(),
                    "category" => $_SESSION['last_category'],
                    "element"  => $choice['type'],
                    "duration" => $duration,
                    "before"   => $result['before'],
                    "after"    => $result['after']
                ];

                // persist updated pokemon object
                
                $_SESSION[$sessionKey][$selectedPokemonId] = serialize($pokemon);
            }

            // prevent reuse of last_choices unless user wants to regenerate
            unset($_SESSION['last_choices']);
            $generatedChoices = null; // hide choices after training
        }
    }

    /* --------------------------
       REST (Istirahat)
       -------------------------- */
    if ($action === 'rest') {
        $selectedPokemonId = (int)($_POST['pokemon'] ?? 0);

        if (!isset($pokemonList[$selectedPokemonId])) {
            $error = "Pokémon tidak ditemukan.";
        } else {
            $pokemon = $pokemonList[$selectedPokemonId];
            // capture full before stats for history (to show hp/atk/def/spd/energy)
            $before = [
                'level' => $pokemon->getLevel(),
                'hp'    => $pokemon->getHP(),
                'atk'   => $pokemon->getAtk(),
                'def'   => $pokemon->getDef(),
                'spd'   => $pokemon->getSpd(),
                'energy'=> $pokemon->getEnergy()
            ];

            $pokemon->rest(); // method in BasePokemon: +20 energy max 100

            $after = [
                'level' => $pokemon->getLevel(),
                'hp'    => $pokemon->getHP(),
                'atk'   => $pokemon->getAtk(),
                'def'   => $pokemon->getDef(),
                'spd'   => $pokemon->getSpd(),
                'energy'=> $pokemon->getEnergy()
            ];

            // persist
            $_SESSION[$sessionKey][$selectedPokemonId] = serialize($pokemon);

            // save history entry (rest)
            $_SESSION['history'][] = [
                "time"   => date("Y-m-d H:i:s"),
                "type"   => "rest",
                "pokemon"=> $pokemon->getName(),
                "before" => $before,
                "after"  => $after
            ];
        }
    }
}

/* helper */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* for display: current selected pokemon object (if exists) */
$currentPokemon = $pokemonList[$selectedPokemonId] ?? reset($pokemonList);
$generatedChoices = $generatedChoices ?? ($_SESSION['last_choices'] ?? null);
$selectedCategory = $selectedCategory ?? ($_SESSION['last_category'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Training Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0a0a0a 100%);
            min-height: 100vh;
        }
        .card {
            background: rgba(30, 30, 30, 0.95);
            backdrop-filter: blur(10px);
        }
        .glass-effect {
            background: rgba(20, 20, 20, 0.8);
            backdrop-filter: blur(20px);
        }
        .glow {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        }
        .glow-green {
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
        }
        .glow-red {
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }
        .glow-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }
        .glow-yellow {
            box-shadow: 0 0 20px rgba(234, 179, 8, 0.3);
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(30, 30, 30, 0.5);
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(139, 92, 246, 0.5);
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(139, 92, 246, 0.7);
        }
    </style>
</head>
<body class="text-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-purple-600 mb-3">
                Pokémon Training Academy
            </h1>
            <p class="text-gray-400 text-lg">Train your Pokémon and unlock powerful moves!</p>
        </div>

        <!-- Error Display -->
        <div id="errorContainer" class="hidden mb-6 p-4 glass-effect border-l-4 border-red-500 rounded-r-lg glow-red">
            <p id="errorText" class="text-red-300"></p>
        </div>

        <!-- Success Display -->
        <div id="successContainer" class="hidden mb-6 p-6 glass-effect border-l-4 border-green-500 rounded-r-lg glow-green">
            <h3 class="text-green-300 text-xl font-bold mb-3">🎉 Training Complete!</h3>
            <div id="successStats" class="grid grid-cols-2 md:grid-cols-6 gap-4 text-sm"></div>
            <div id="unlockedMoves" class="hidden mt-4 pt-4 border-t border-green-900">
                <p class="text-green-300 font-semibold">✨ New Moves Unlocked:</p>
                <div id="movesList" class="flex flex-wrap gap-2 mt-2"></div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column - Pokemon Selection & Stats -->
            <div class="space-y-6">
                <!-- Pokemon Selector -->
                <div class="card rounded-2xl shadow-2xl p-6 border border-gray-800">
                    <h2 class="text-2xl font-bold text-purple-400 mb-4">Choose Your Pokémon</h2>
                    <div id="pokemonList" class="space-y-3"></div>
                </div>

                <!-- Stats Display -->
                <div id="statsCard" class="card rounded-2xl shadow-2xl p-6 border border-gray-800"></div>

                <!-- Reset Button -->
                <button onclick="resetAll()" class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-all shadow-lg glow-red font-semibold">
                    🔄 Reset All Progress
                </button>
            </div>

            <!-- Middle Column - Training Panel -->
            <div>
                <div class="card rounded-2xl shadow-2xl p-6 border border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-2xl glow">
                            💪
                        </div>
                        <h2 class="text-2xl font-bold text-purple-400">Training Center</h2>
                    </div>

                    <!-- Category Selection -->
                    <div class="mb-6">
                        <label class="block text-gray-300 mb-3 font-semibold">Training Category</label>
                        <div class="grid grid-cols-3 gap-3" id="categoryButtons">
                            <button onclick="selectCategory('Attack')" class="category-btn px-4 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 hover:text-purple-300 transition-all">
                                Attack
                            </button>
                            <button onclick="selectCategory('Defense')" class="category-btn px-4 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 hover:text-purple-300 transition-all">
                                Defense
                            </button>
                            <button onclick="selectCategory('Speed')" class="category-btn px-4 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 hover:text-purple-300 transition-all">
                                Speed
                            </button>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <button id="generateBtn" onclick="generateChoices()" class="hidden w-full mb-6 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white rounded-lg transition-all shadow-lg glow font-semibold">
                        🎲 Generate Training Options
                    </button>

                    <!-- Training Choices -->
                    <div id="trainingChoices" class="hidden mb-6">
                        <label class="block text-gray-300 mb-3 font-semibold">Select Training</label>
                        <div id="choicesList" class="space-y-3"></div>
                    </div>

                    <!-- Duration Selection -->
                    <div id="durationSection" class="hidden mb-6">
                        <label class="block text-gray-300 mb-3 font-semibold">Training Duration</label>
                        <div class="grid grid-cols-3 gap-3" id="durationButtons">
                            <button onclick="selectDuration(10)" class="duration-btn px-3 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 transition-all">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-2xl">⏱️</span>
                                    <span class="text-sm font-semibold">10 min</span>
                                    <span class="text-xs text-gray-500">-10 energy</span>
                                </div>
                            </button>
                            <button onclick="selectDuration(20)" class="duration-btn px-3 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 transition-all">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-2xl">⏱️</span>
                                    <span class="text-sm font-semibold">20 min</span>
                                    <span class="text-xs text-gray-500">-20 energy</span>
                                </div>
                            </button>
                            <button onclick="selectDuration(30)" class="duration-btn px-3 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 transition-all">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-2xl">⏱️</span>
                                    <span class="text-sm font-semibold">30 min</span>
                                    <span class="text-xs text-gray-500">-30 energy</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Train Button -->
                    <button id="trainBtn" onclick="startTraining()" class="hidden w-full px-4 py-4 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 disabled:from-gray-700 disabled:to-gray-800 disabled:text-gray-500 text-white rounded-lg transition-all shadow-lg font-bold text-lg disabled:cursor-not-allowed">
                        💪 Start Training
                    </button>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-3 opacity-50">💪</div>
                        <p>Select a category to begin training</p>
                    </div>
                </div>
            </div>

            <!-- Right Column - History -->
            <div>
                <div class="card rounded-2xl shadow-2xl p-6 border border-gray-800">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-2xl glow-blue">
                            📜
                        </div>
                        <h2 class="text-2xl font-bold text-blue-400">Training History</h2>
                    </div>

                    <div id="historyList" class="space-y-3 max-h-[600px] overflow-y-auto scrollbar-thin">
                        <div class="text-center py-12 text-gray-500">
                            <div class="text-6xl mb-3 opacity-50">📜</div>
                            <p>No training history yet</p>
                            <p class="text-sm mt-1">Start training to see history</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Training Descriptions
        const TRAINING_DESCRIPTIONS = {
            "Attack": {
                "Electric": "Penyaluran Voltase Puncak: Fokus Petir Terpusat",
                "Grass": "Badai Daun Silet: Latihan Ketajaman Klorofil",
                "Fire": "Semburan Inferno: Pernapasan Inti Magma",
                "Water": "Meriam Hidro: Kompresi Tekanan Air Absolut",
            },
            "Defense": {
                "Electric": "Jubah Medan Magnet: Tolakan Statis",
                "Grass": "Meditasi Akar Tua: Pengerasan Kulit Kayu",
                "Fire": "Tameng Uap Panas: Evaporasi Serangan Air",
                "Water": "Zirah Cairan Non-Newtonian: Adaptasi Benturan",
            },
            "Speed": {
                "Electric": "Transmisi Syaraf Kilat: Refleks Kecepatan Cahaya",
                "Grass": "Luncuran Fotosintesis: Manuver Hutan Rimba",
                "Fire": "Akselerasi Roket Pijar: Ledakan Langkah Pembakaran",
                "Water": "Aliran Arus Deras: Teknik Renang Aerodinamis",
            }
        };

        // Element Moves
        const ELEMENT_MOVES = {
            "Electric": {
                10: "Spark ⚡",
                20: "Thunder Bolt ⚡⚡",
                30: "Volt Tackle ⚡💥"
            },
            "Grass": {
                10: "Vine Whip 🌿",
                20: "Razor Leaf 🍃",
                30: "Seed Bomb 🌱💥"
            },
            "Fire": {
                10: "Ember 🔥",
                20: "Fire Fang 🔥🐾",
                30: "Flamethrower 🔥💨"
            },
            "Water": {
                10: "Water Gun 💧",
                20: "Water Pulse 🌊",
                30: "Hydro Pump 💦💥"
            }
        };

        // Type configurations
        const TYPE_CONFIG = {
            "Electric": {
                icon: "⚡",
                gradient: "from-yellow-400 to-yellow-600",
                bg: "bg-yellow-900 bg-opacity-20 border-yellow-700",
                glow: "glow-yellow"
            },
            "Grass": {
                icon: "🌿",
                gradient: "from-green-400 to-green-600",
                bg: "bg-green-900 bg-opacity-20 border-green-700",
                glow: "glow-green"
            },
            "Fire": {
                icon: "🔥",
                gradient: "from-orange-400 to-red-600",
                bg: "bg-orange-900 bg-opacity-20 border-red-700",
                glow: "glow-red"
            },
            "Water": {
                icon: "💧",
                gradient: "from-blue-400 to-blue-600",
                bg: "bg-blue-900 bg-opacity-20 border-blue-700",
                glow: "glow-blue"
            }
        };

        // State
        let pokemonList = [];
        let selectedPokemonId = 1;
        let selectedCategory = null;
        let trainingChoices = null;
        let selectedChoice = null;
        let selectedDuration = null;
        let history = [];

        // Initialize
        function init() {
            loadFromStorage();
            if (pokemonList.length === 0) {
                pokemonList = createInitialPokemon();
                saveToStorage();
            }
            render();
        }

        function createInitialPokemon() {
            return [
                { id: 1, name: "Raichu", type: "Electric", level: 1, hp: 300, atk: 12, def: 8, spd: 10, energy: 100, moves: ["Thunder Shock ⚡"] },
                { id: 2, name: "Bulbasaur", type: "Grass", level: 1, hp: 300, atk: 8, def: 12, spd: 6, energy: 100, moves: ["Tackle 🌿"] },
                { id: 3, name: "Charmander", type: "Fire", level: 1, hp: 300, atk: 11, def: 9, spd: 8, energy: 100, moves: ["Ember Spark 🔥"] },
                { id: 4, name: "Squirtle", type: "Water", level: 1, hp: 300, atk: 9, def: 11, spd: 7, energy: 100, moves: ["Bubble Shot 💧"] }
            ];
        }

        function loadFromStorage() {
            const saved = localStorage.getItem('pokemonList');
            const savedHistory = localStorage.getItem('history');
            if (saved) pokemonList = JSON.parse(saved);
            if (savedHistory) history = JSON.parse(savedHistory);
        }

        function saveToStorage() {
            localStorage.setItem('pokemonList', JSON.stringify(pokemonList));
            localStorage.setItem('history', JSON.stringify(history));
        }

        function getCurrentPokemon() {
            return pokemonList.find(p => p.id === selectedPokemonId);
        }

        function selectPokemon(id) {
            selectedPokemonId = id;
            render();
        }

        function selectCategory(category) {
            selectedCategory = category;
            trainingChoices = null;
            selectedChoice = null;
            selectedDuration = null;
            hideError();
            hideSuccess();
            render();
        }

        function generateChoices() {
            const pokemon = getCurrentPokemon();
            if (!selectedCategory) {
                showError('Please select a training category first.');
                return;
            }

            hideError();
            hideSuccess();

            const categoryData = TRAINING_DESCRIPTIONS[selectedCategory];
            
            // Best match
            const best = {
                type: pokemon.type,
                text: categoryData[pokemon.type]
            };

            // Random other type
            const allTypes = ["Electric", "Grass", "Fire", "Water"];
            const otherTypes = allTypes.filter(t => t !== pokemon.type);
            const randomType = otherTypes[Math.floor(Math.random() * otherTypes.length)];
            const randomTraining = {
                type: randomType,
                text: categoryData[randomType]
            };

            // Neutral
            const neutral = {
                type: "Neutral",
                text: "Latihan Otodidak: Adaptasi Insting"
            };

            // Shuffle
            const choices = [best, randomTraining, neutral];
            for (let i = choices.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [choices[i], choices[j]] = [choices[j], choices[i]];
            }

            trainingChoices = choices;
            selectedChoice = null;
            selectedDuration = null;
            render();
        }

        function selectChoice(index) {
            selectedChoice = index;
            render();
        }

        function selectDuration(duration) {
            selectedDuration = duration;
            render();
        }

        function startTraining() {
            hideError();
            hideSuccess();

            const pokemon = getCurrentPokemon();
            if (!trainingChoices || selectedChoice === null) {
                showError('Please select a training option first.');
                return;
            }
            if (selectedDuration === null) {
                showError('Please select training duration.');
                return;
            }

            const choice = trainingChoices[selectedChoice];
            const result = processTraining(pokemon, choice.type, selectedCategory, selectedDuration);

            if (!result.success) {
                showError(result.message);
                return;
            }

            // Update pokemon
            const index = pokemonList.findIndex(p => p.id === selectedPokemonId);
            pokemonList[index] = pokemon;

            // Add to history
            history.unshift({
                time: new Date().toLocaleString(),
                type: 'train',
                pokemon: pokemon.name,
                category: selectedCategory,
                element: choice.type,
                duration: selectedDuration,
                before: result.before,
                after: result.after
            });

            saveToStorage();
            showSuccess(result);

            // Clear selections
            trainingChoices = null;
            selectedChoice = null;
            selectedDuration = null;

            render();
        }

        function processTraining(pokemon, trainingElement, category, duration) {
            // Capture before state
            const before = {
                level: pokemon.level,
                hp: pokemon.hp,
                atk: pokemon.atk,
                def: pokemon.def,
                spd: pokemon.spd,
                energy: pokemon.energy,
                moves: [...pokemon.moves]
            };

            // Calculate energy cost
            const energyCost = (duration / 10) * 10;

            // Check energy
            if (pokemon.energy < energyCost) {
                return {
                    success: false,
                    message: "Energi Pokémon tidak cukup untuk melakukan latihan!",
                    before: before,
                    after: before
                };
            }

            // Deduct energy
            pokemon.energy -= energyCost;

            // Calculate level gain
            let levelGain = duration / 10;
            let bonus = 0;

            // IMPORTANT: Random bonus if element matches (1 or 2)
            if (pokemon.type === trainingElement) {
                bonus = Math.floor(Math.random() * 2) + 1; // Random 1 or 2
            }

            const totalGain = levelGain + bonus;

            // Update level
            pokemon.level += totalGain;

            // Calculate HP
            let hpInc = 0;
            for (let i = 1; i <= totalGain; i++) {
                const newLv = before.level + i;
                if (newLv % 5 === 0) {
                    hpInc += 150;
                } else {
                    hpInc += 100;
                }
            }
            pokemon.hp += hpInc;

            // Update stats by category
            if (category === "Attack") pokemon.atk += 20;
            if (category === "Defense") pokemon.def += 10;
            if (category === "Speed") pokemon.spd += 5;

            // Unlock moves
            const movesUnlocked = [];
            const allMoves = ELEMENT_MOVES[pokemon.type];
            
            for (const [reqLevel, move] of Object.entries(allMoves)) {
                const lvl = parseInt(reqLevel);
                if (before.level < lvl && pokemon.level >= lvl) {
                    movesUnlocked.push(move);
                    pokemon.moves.push(move);
                }
            }

            // After state
            const after = {
                level: pokemon.level,
                hp: pokemon.hp,
                atk: pokemon.atk,
                def: pokemon.def,
                spd: pokemon.spd,
                energy: pokemon.energy,
                moves: [...pokemon.moves]
            };

            return {
                success: true,
                message: "Latihan berhasil!",
                before: before,
                after: after,
                unlockedMoves: movesUnlocked
            };
        }

        function rest() {
            hideError();
            hideSuccess();

            const pokemon = getCurrentPokemon();
            
            const before = {
                level: pokemon.level,
                hp: pokemon.hp,
                atk: pokemon.atk,
                def: pokemon.def,
                spd: pokemon.spd,
                energy: pokemon.energy,
                moves: [...pokemon.moves]
            };

            pokemon.energy += 20;
            if (pokemon.energy > 100) {
                pokemon.energy = 100;
            }

            const after = {
                level: pokemon.level,
                hp: pokemon.hp,
                atk: pokemon.atk,
                def: pokemon.def,
                spd: pokemon.spd,
                energy: pokemon.energy,
                moves: [...pokemon.moves]
            };

            // Update pokemon
            const index = pokemonList.findIndex(p => p.id === selectedPokemonId);
            pokemonList[index] = pokemon;

            // Add to history
            history.unshift({
                time: new Date().toLocaleString(),
                type: 'rest',
                pokemon: pokemon.name,
                before: before,
                after: after
            });

            saveToStorage();
            render();
        }

        function resetAll() {
            if (confirm('Are you sure you want to reset all progress? This cannot be undone.')) {
                pokemonList = createInitialPokemon();
                history = [];
                selectedCategory = null;
                trainingChoices = null;
                selectedChoice = null;
                selectedDuration = null;
                saveToStorage();
                hideError();
                hideSuccess();
                render();
            }
        }

        function showError(message) {
            document.getElementById('errorContainer').classList.remove('hidden');
            document.getElementById('errorText').textContent = message;
        }

        function hideError() {
            document.getElementById('errorContainer').classList.add('hidden');
        }

        function showSuccess(result) {
            const container = document.getElementById('successContainer');
            const statsDiv = document.getElementById('successStats');
            const movesContainer = document.getElementById('unlockedMoves');
            const movesList = document.getElementById('movesList');

            container.classList.remove('hidden');

            // Stats
            statsDiv.innerHTML = `
                <div><span class="text-gray-400">Level:</span><div class="text-green-300 font-semibold">${result.before.level} → ${result.after.level}</div></div>
                <div><span class="text-gray-400">HP:</span><div class="text-green-300 font-semibold">${result.before.hp} → ${result.after.hp}</div></div>
                <div><span class="text-gray-400">ATK:</span><div class="text-green-300 font-semibold">${result.before.atk} → ${result.after.atk}</div></div>
                <div><span class="text-gray-400">DEF:</span><div class="text-green-300 font-semibold">${result.before.def} → ${result.after.def}</div></div>
                <div><span class="text-gray-400">SPD:</span><div class="text-green-300 font-semibold">${result.before.spd} → ${result.after.spd}</div></div>
                <div><span class="text-gray-400">Energy:</span><div class="text-green-300 font-semibold">${result.before.energy} → ${result.after.energy}</div></div>
            `;

            // Unlocked moves
            if (result.unlockedMoves && result.unlockedMoves.length > 0) {
                movesContainer.classList.remove('hidden');
                movesList.innerHTML = result.unlockedMoves.map(move => 
                    `<span class="px-3 py-1 bg-green-900 bg-opacity-50 text-green-300 rounded-full text-sm border border-green-700">${move}</span>`
                ).join('');
            } else {
                movesContainer.classList.add('hidden');
            }
        }

        function hideSuccess() {
            document.getElementById('successContainer').classList.add('hidden');
        }

        function render() {
            renderPokemonList();
            renderStats();
            renderTrainingPanel();
            renderHistory();
        }

        function renderPokemonList() {
            const container = document.getElementById('pokemonList');
            container.innerHTML = pokemonList.map(pokemon => {
                const config = TYPE_CONFIG[pokemon.type];
                const isSelected = pokemon.id === selectedPokemonId;
                return `
                    <button onclick="selectPokemon(${pokemon.id})" class="w-full p-4 rounded-xl border-2 transition-all ${
                        isSelected 
                            ? `${config.bg} border-current shadow-lg ${config.glow} scale-105` 
                            : 'border-gray-700 bg-gray-900 hover:border-gray-600'
                    }">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br ${config.gradient} flex items-center justify-center text-2xl shadow-lg">
                                ${config.icon}
                            </div>
                            <div class="flex-1 text-left">
                                <div class="text-gray-100 font-semibold">${pokemon.name}</div>
                                <div class="text-gray-400 text-sm">${pokemon.type} Type</div>
                            </div>
                            ${isSelected ? '<div class="w-3 h-3 rounded-full bg-gradient-to-br from-green-400 to-green-600"></div>' : ''}
                        </div>
                    </button>
                `;
            }).join('');
        }

        function renderStats() {
            const pokemon = getCurrentPokemon();
            const container = document.getElementById('statsCard');
            const energyPercentage = pokemon.energy;
            const energyColor = energyPercentage > 60 ? 'bg-green-500' : energyPercentage > 30 ? 'bg-yellow-500' : 'bg-red-500';

            container.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-2xl font-bold text-gray-100">${pokemon.name}</h3>
                    <span class="px-3 py-1 rounded-full bg-gradient-to-r from-purple-900 to-pink-900 text-purple-300 font-semibold border border-purple-700">
                        Level ${pokemon.level}
                    </span>
                </div>

                <div class="space-y-4">
                    <!-- HP -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-900 bg-opacity-50 flex items-center justify-center text-red-400 border border-red-800">
                            ❤️
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">HP</span>
                                <span class="text-gray-100 font-semibold">${pokemon.hp}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ATK -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-900 bg-opacity-50 flex items-center justify-center text-orange-400 border border-orange-800">
                            ⚔️
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Attack</span>
                                <span class="text-gray-100 font-semibold">${pokemon.atk}</span>
                            </div>
                        </div>
                    </div>

                    <!-- DEF -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-900 bg-opacity-50 flex items-center justify-center text-blue-400 border border-blue-800">
                            🛡️
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Defense</span>
                                <span class="text-gray-100 font-semibold">${pokemon.def}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SPD -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-900 bg-opacity-50 flex items-center justify-center text-yellow-400 border border-yellow-800">
                            ⚡
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Speed</span>
                                <span class="text-gray-100 font-semibold">${pokemon.spd}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Energy -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-900 bg-opacity-50 flex items-center justify-center text-green-400 border border-green-800">
                            🔋
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-400">Energy</span>
                                <span class="text-gray-100 font-semibold">${pokemon.energy}</span>
                            </div>
                            <div class="h-2 bg-gray-800 rounded-full overflow-hidden border border-gray-700">
                                <div class="${energyColor} h-full transition-all duration-300" style="width: ${energyPercentage}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Moves -->
                <div class="mt-6 pt-6 border-t border-gray-800">
                    <h4 class="text-gray-300 mb-3 font-semibold">Moves</h4>
                    <div class="flex flex-wrap gap-2">
                        ${pokemon.moves.map(move => `
                            <span class="px-3 py-1 bg-gradient-to-r from-indigo-900 to-purple-900 text-indigo-300 rounded-full text-sm border border-indigo-800">
                                ${move}
                            </span>
                        `).join('')}
                    </div>
                </div>

                <!-- Rest Button -->
                <button onclick="rest()" ${pokemon.energy >= 100 ? 'disabled' : ''} class="mt-6 w-full px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 disabled:from-gray-700 disabled:to-gray-800 disabled:text-gray-500 text-white rounded-lg transition-all shadow-lg font-semibold disabled:cursor-not-allowed ${pokemon.energy < 100 ? 'glow-green' : ''}">
                    😴 Rest (+20 Energy)
                </button>
            `;
        }

        function renderTrainingPanel() {
            // Update category buttons
            const categoryBtns = document.querySelectorAll('.category-btn');
            categoryBtns.forEach((btn, index) => {
                const categories = ['Attack', 'Defense', 'Speed'];
                const category = categories[index];
                if (selectedCategory === category) {
                    btn.className = 'category-btn px-4 py-3 rounded-lg border-2 border-purple-500 bg-purple-900 bg-opacity-30 text-purple-300 transition-all glow';
                } else {
                    btn.className = 'category-btn px-4 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 hover:text-purple-300 transition-all';
                }
            });

            // Show/hide generate button
            const generateBtn = document.getElementById('generateBtn');
            const emptyState = document.getElementById('emptyState');
            if (selectedCategory) {
                generateBtn.classList.remove('hidden');
                emptyState.classList.add('hidden');
            } else {
                generateBtn.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }

            // Training choices
            const choicesSection = document.getElementById('trainingChoices');
            const choicesList = document.getElementById('choicesList');
            const durationSection = document.getElementById('durationSection');
            const trainBtn = document.getElementById('trainBtn');

            if (trainingChoices) {
                choicesSection.classList.remove('hidden');
                durationSection.classList.remove('hidden');
                trainBtn.classList.remove('hidden');

                const pokemon = getCurrentPokemon();
                choicesList.innerHTML = trainingChoices.map((choice, index) => {
                    const isMatching = choice.type === pokemon.type;
                    const isSelected = selectedChoice === index;
                    return `
                        <button onclick="selectChoice(${index})" class="w-full p-4 rounded-xl border-2 text-left transition-all ${
                            isSelected 
                                ? 'border-purple-500 bg-purple-900 bg-opacity-30 shadow-lg glow' 
                                : 'border-gray-700 bg-gray-900 hover:border-gray-600'
                        }">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <div class="text-gray-100 text-sm mb-1 font-semibold">${choice.text}</div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">Element: ${choice.type}</span>
                                        ${isMatching ? '<span class="px-2 py-0.5 bg-green-900 bg-opacity-50 text-green-300 rounded text-xs border border-green-700">⚡ Bonus +1-2 Level</span>' : ''}
                                    </div>
                                </div>
                                ${isSelected ? '<div class="w-3 h-3 rounded-full bg-purple-500 mt-1"></div>' : ''}
                            </div>
                        </button>
                    `;
                }).join('');

                // Update duration buttons
                const durationBtns = document.querySelectorAll('.duration-btn');
                const durations = [10, 20, 30];
                durationBtns.forEach((btn, index) => {
                    const duration = durations[index];
                    if (selectedDuration === duration) {
                        btn.className = 'duration-btn px-3 py-3 rounded-lg border-2 border-purple-500 bg-purple-900 bg-opacity-30 text-purple-300 transition-all glow';
                    } else {
                        btn.className = 'duration-btn px-3 py-3 rounded-lg border-2 border-gray-700 bg-gray-900 text-gray-400 hover:border-purple-500 transition-all';
                    }
                });

                // Update train button
                if (selectedChoice !== null && selectedDuration !== null) {
                    trainBtn.disabled = false;
                    trainBtn.textContent = '💪 Start Training';
                } else {
                    trainBtn.disabled = true;
                    trainBtn.textContent = '⚠️ Select Training & Duration';
                }
            } else {
                choicesSection.classList.add('hidden');
                durationSection.classList.add('hidden');
                trainBtn.classList.add('hidden');
            }
        }

        function renderHistory() {
            const container = document.getElementById('historyList');
            
            if (history.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-3 opacity-50">📜</div>
                        <p>No training history yet</p>
                        <p class="text-sm mt-1">Start training to see history</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = history.map(entry => {
                const isTraining = entry.type === 'train';
                return `
                    <div class="p-4 rounded-xl glass-effect border border-gray-800">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="${isTraining ? 'bg-orange-900 bg-opacity-50 text-orange-400 border-orange-800' : 'bg-green-900 bg-opacity-50 text-green-400 border-green-800'} w-10 h-10 rounded-lg flex items-center justify-center border">
                                ${isTraining ? '📈' : '😴'}
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-100 font-semibold">${entry.pokemon}</div>
                                <div class="text-xs text-gray-500">${entry.time}</div>
                            </div>
                        </div>

                        ${isTraining ? `
                            <div class="text-xs text-gray-400 mb-2 pl-13">
                                <div>${entry.category} Training</div>
                                <div>Element: ${entry.element} • ${entry.duration} min</div>
                            </div>
                        ` : ''}

                        <div class="grid grid-cols-3 gap-2 text-xs pl-13">
                            ${entry.before.level !== entry.after.level ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-purple-800">
                                    <div class="text-gray-500">Level</div>
                                    <div class="text-purple-400 font-semibold">${entry.before.level} → ${entry.after.level}</div>
                                </div>
                            ` : ''}
                            ${entry.before.hp !== entry.after.hp ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-red-800">
                                    <div class="text-gray-500">HP</div>
                                    <div class="text-red-400 font-semibold">${entry.before.hp} → ${entry.after.hp}</div>
                                </div>
                            ` : ''}
                            ${entry.before.atk !== entry.after.atk ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-orange-800">
                                    <div class="text-gray-500">ATK</div>
                                    <div class="text-orange-400 font-semibold">${entry.before.atk} → ${entry.after.atk}</div>
                                </div>
                            ` : ''}
                            ${entry.before.def !== entry.after.def ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-blue-800">
                                    <div class="text-gray-500">DEF</div>
                                    <div class="text-blue-400 font-semibold">${entry.before.def} → ${entry.after.def}</div>
                                </div>
                            ` : ''}
                            ${entry.before.spd !== entry.after.spd ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-yellow-800">
                                    <div class="text-gray-500">SPD</div>
                                    <div class="text-yellow-400 font-semibold">${entry.before.spd} → ${entry.after.spd}</div>
                                </div>
                            ` : ''}
                            ${entry.before.energy !== entry.after.energy ? `
                                <div class="bg-gray-900 bg-opacity-50 rounded-lg p-2 border border-green-800">
                                    <div class="text-gray-500">Energy</div>
                                    <div class="text-green-400 font-semibold">${entry.before.energy} → ${entry.after.energy}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Initialize on load
        init();
    </script>
</body>
</html>
