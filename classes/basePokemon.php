<?php
require_once "pokemon.php";

abstract class BasePokemon implements Pokemon {
    public $name;
    public $type;
    public $level;
    public $hp;
    public $moves = [];

    // new stats
    public $atk;
    public $def;
    public $spd;
    public $energy;

    public function __construct($name, $type, $level = 1, $hp = 300, $energy = 100, $atk = 10, $def = 10, $spd = 5) {
        $this->name   = $name;
        $this->type   = $type;
        $this->level  = $level;
        $this->hp     = $hp;
        $this->energy = $energy;

        $this->atk  = $atk;
        $this->def  = $def;
        $this->spd  = $spd;

        $this->moves = []; // start empty
    }

    // getters required by interface
    public function getName()  { return $this->name; }
    public function getType()  { return $this->type; }
    public function getLevel() { return $this->level; }
    public function getHP()    { return $this->hp; }
    public function getAtk()   { return $this->atk; }
    public function getDef()   { return $this->def; }
    public function getSpd()   { return $this->spd; }
    public function getEnergy(){ return $this->energy; }
    public function getMoves() { return $this->moves; }

    // default, species can override
    public function specialMove() {
        return "No special move";
    }
    public function rest() {
    $this->energy += 20;
    if ($this->energy > 100) {
        $this->energy = 100;
    }
}

}
