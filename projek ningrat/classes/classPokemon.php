<?php
require_once "basePokemon.php";

class ElectricPokemon extends BasePokemon {
    public function __construct($name = "Raichu") {
        parent::__construct($name, "Electric", 1, 300, 100, 12, 8, 10);
        $this->moves[] = "Thunder Shock ⚡";
    }
    public function specialMove() { return "Thunder Shock ⚡"; }
}

class GrassPokemon extends BasePokemon {
    public function __construct($name = "Bulbasaur") {
        parent::__construct($name, "Grass", 1, 300, 100, 8, 12, 6);
        $this->moves[] = "Tackle 🌿";
    }
    public function specialMove() { return "Tackle 🌿"; }
}

class FirePokemon extends BasePokemon {
    public function __construct($name = "Charmander") {
        parent::__construct($name, "Fire", 1, 300, 100, 11, 9, 8);
        $this->moves[] = "Ember Spark 🔥";
    }
    public function specialMove() { return "Ember Spark 🔥"; }
}

class WaterPokemon extends BasePokemon {
    public function __construct($name = "Squirtle") {
        parent::__construct($name, "Water", 1, 300, 100, 9, 11, 7);
        $this->moves[] = "Bubble Shot 💧";
    }
    public function specialMove() { return "Bubble Shot 💧"; }
}
