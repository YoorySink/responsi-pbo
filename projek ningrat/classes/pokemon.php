<?php
interface Pokemon {
    public function getName();
    public function getType();
    public function getLevel();
    public function getHP();
    public function getAtk();
    public function getDef();
    public function getSpd();
    public function getEnergy();
    public function getMoves();
    public function specialMove(); //override
}
