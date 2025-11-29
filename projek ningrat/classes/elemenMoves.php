<?php
class ElementMoves {
    public static $moves = [
        "Electric" => [10 => "Spark ⚡", 20 => "Thunder Bolt ⚡⚡", 30 => "Volt Tackle ⚡💥"],
        "Grass"    => [10 => "Vine Whip 🌿", 20 => "Razor Leaf 🍃", 30 => "Seed Bomb 🌱💥"],
        "Fire"     => [10 => "Ember 🔥", 20 => "Fire Fang 🔥🐾", 30 => "Flamethrower 🔥💨"],
        "Water"    => [10 => "Water Gun 💧", 20 => "Water Pulse 🌊", 30 => "Hydro Pump 💦💥"],
    ];

    public static function getMove($type, $level) {
        return self::$moves[$type][$level] ?? null;
    }
}
