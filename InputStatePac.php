<?php

class InputStatePac
{
    public $pacId;
    public $mine;
    public $x;
    public $y;
    public $typeId;
    public $speedTurnsLeft;
    public $abilityCooldown;

    public function __construct($pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown)
    {
        $this->pacId = $pacId;
        $this->mine = $mine;
        $this->x = $x;
        $this->y = $y;
        $this->typeId = $typeId;
        $this->speedTurnsLeft = $speedTurnsLeft;
        $this->abilityCooldown = $abilityCooldown;
    }
}