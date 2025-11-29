<?php
require_once "autoload.php";
$pokemonList = [
    1 => new ElectricPokemon("Raichu"),
    2 => new GrassPokemon("Bulbasaur"),
    3 => new FirePokemon("Charmander"),
    4 => new WaterPokemon("Squirtle"),
];

echo "=== PILIH POKÉMON ===\n";
foreach ($pokemonList as $k=>$p) {
    echo "[$k] {$p->getName()} ({$p->getType()})\n";
}
echo "Masukkan nomor Pokémon: ";
$choice = trim(fgets(STDIN));
if (!isset($pokemonList[$choice])) { echo "Pilihan tidak valid\n"; exit; }
$pokemon = $pokemonList[$choice];

echo "\n=== INFO AWAL POKÉMON ===\n";
echo "Nama  : {$pokemon->getName()}\n";
echo "Tipe  : {$pokemon->getType()}\n";
echo "Level : {$pokemon->getLevel()}\n";
echo "HP    : {$pokemon->getHP()}\n";
echo "ATK   : {$pokemon->getAtk()}\n";
echo "DEF   : {$pokemon->getDef()}\n";
echo "SPD   : {$pokemon->getSpd()}\n";
echo "Energy: {$pokemon->getEnergy()}\n\n";

$categories = ["Attack","Defense","Speed"];
echo "=== PILIH KATEGORI LATIHAN ===\n";
foreach ($categories as $i=>$c) echo "[".($i+1)."] $c\n";
echo "Masukkan pilihan kategori: ";
$catChoice = trim(fgets(STDIN));
if (!isset($categories[$catChoice-1])) { echo "Kategori tidak valid\n"; exit; }
$category = $categories[$catChoice-1];

// generate 3 choices
$choices = Training::generateChoices($pokemon->getType(), $category);
echo "\n=== PILIHAN LATIHAN ===\n";
foreach ($choices as $i=>$c) {
    echo "[".($i+1)."] {$c['text']} (Element: {$c['type']})\n";
}
echo "Pilih latihan (1-3): ";
$pick = (int)trim(fgets(STDIN));
if (!isset($choices[$pick-1])) { echo "Pilihan latihan tidak valid\n"; exit; }
$selected = $choices[$pick-1];

echo "\n=== PILIH DURASI LATIHAN ===\n";
echo "[1] 10 menit\n";
echo "[2] 20 menit\n";
echo "[3] 30 menit\n";

echo "Pilih durasi (1-3): ";
$durChoice = trim(fgets(STDIN));

$durations = [
    1 => 10,
    2 => 20,
    3 => 30
];

if (!isset($durations[$durChoice])) {
    echo "Pilihan durasi tidak valid!\n";
    exit;
}

$duration = $durations[$durChoice];


$result = Training::process($pokemon, $selected["type"], $category, $duration);
if (!$result["success"]) { echo $result["message"]."\n"; exit; }

echo "\n=== HASIL LATIHAN ===\n";
echo "Latihan dipilih : {$selected['text']}\n";
echo "Elemen latihan  : {$selected['type']}\n\n";

$b = $result["before"];
$a = $result["after"];
echo "Level : {$b['level']} -> {$a['level']}\n";
echo "HP    : {$b['hp']} -> {$a['hp']}\n";
echo "ATK   : {$b['atk']} -> {$a['atk']}\n";
echo "DEF   : {$b['def']} -> {$a['def']}\n";
echo "SPD   : {$b['spd']} -> {$a['spd']}\n";
echo "Energy: {$b['energy']} -> {$a['energy']}\n\n";

echo "Jurus aktif:\n";
foreach ($a['moves'] as $m) echo "- $m\n";
echo "\nLatihan selesai.\n";
