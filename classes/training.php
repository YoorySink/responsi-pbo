<?php
require_once "trainingDescriptions.php";
require_once "elemenMoves.php";

class Training {

    // buat 3 pilihan
    public static function generateChoices($pokemonType, $category) {
        $list = TrainingDescriptions::$data[$category];
        $best = $list[$pokemonType];
        $keys = array_keys($list);
        $keys = array_filter($keys, fn($e) => $e != $pokemonType);
        $randomElem = $keys[array_rand($keys)];
        $randomTraining = $list[$randomElem];
        $default = "Latihan Otodidak: Adaptasi Insting";

        $choices = [
            ["type"=>$pokemonType, "text"=>$best],
            ["type"=>$randomElem,  "text"=>$randomTraining],
            ["type"=>"Neutral",    "text"=>$default],
        ];

        shuffle($choices);
        return $choices;
    }

    // process training: kategori affects stats
    // $category nya adalah atk def spd
    public static function process($pokemon, $trainingElement, $category, $duration){
    // ---- STATE AWAL (SELALU ADA, bahkan kalau gagal)
    $before = [
        "level"  => $pokemon->getLevel(),
        "hp"     => $pokemon->getHP(),
        "atk"    => $pokemon->getAtk(),
        "def"    => $pokemon->getDef(),
        "spd"    => $pokemon->getSpd(),
        "energy" => $pokemon->getEnergy(),
        "moves"  => $pokemon->moves
    ];

    // ---- Hitung energi yang dibutuhkan per durasi
    $costPer10 = 10;                     // 10 energy per 10 menit
    $energyCost = ($duration / 10) * $costPer10;

    // ---- Jika ENERGI TIDAK CUKUP → GAGAL
    if ($pokemon->getEnergy() < $energyCost) {
        return [
            "success" => false,
            "message" => "Energi Pokémon tidak cukup untuk melakukan latihan!",
            "before"  => $before,
            "after"   => $before  
        ];
    }

    // ---- Kurangi energy
    $pokemon->energy -= $energyCost;

    // ---- Naik level berdasarkan durasi
    $levelGain = $duration / 10; // 10 menit = 1 level
    $bonus = 0;

    // ---- Bonus elemen jika cocok
    if ($pokemon->getType() == $trainingElement) {
        $bonus = rand(1, 2); // bonus level random 1–2
    }

    $totalGain = $levelGain + $bonus;

    // ---- Update level
    $pokemon->level += $totalGain;

    // ---- Hitung stat baru
    // HP +100 per level, dan setiap level 5, 10, 15, 20, 25, 30 → +150
    $hpInc = 0;
    for ($i = 1; $i <= $totalGain; $i++) {
        $newLv = $before['level'] + $i;
        if ($newLv % 5 == 0) {
            $hpInc += 150;
        } else {
            $hpInc += 100;
        }
    }
    $pokemon->hp += $hpInc;

    // ---- Stat sesuai kategori
    if ($category == "Attack")  $pokemon->atk += 20;
    if ($category == "Defense") $pokemon->def += 10;
    if ($category == "Speed")   $pokemon->spd += 5;

    // ---- Unlock moves
    $movesUnlocked = [];
    $allMoves = ElementMoves::$moves[$pokemon->getType()];

    foreach ($allMoves as $reqLevel => $mv) {
        if ($before['level'] < $reqLevel && $pokemon->level >= $reqLevel) {
            $movesUnlocked[] = $mv;
            $pokemon->moves[] = $mv;
        }
    }

    // ---- STATE SESUDAH LATIHAN (format lengkap)
    $after = [
        "level"  => $pokemon->getLevel(),
        "hp"     => $pokemon->getHP(),
        "atk"    => $pokemon->getAtk(),
        "def"    => $pokemon->getDef(),
        "spd"    => $pokemon->getSpd(),
        "energy" => $pokemon->getEnergy(),
        "moves"  => $pokemon->moves
    ];

    return [
        "success" => true,
        "message" => "Latihan berhasil!",
        "before"  => $before,
        "after"   => $after,
        "unlockedMoves" => $movesUnlocked
    ];
    }

}
