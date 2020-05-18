<?php

class State
{
//    public $i = 0;

//    public $key;

//    public $turn;

    public $myScore = 0;
//    public $opScore;
//    public $map;

    /**
     * @var StateMyPac[]
     */
    public $myPacs = [];

    public $opPacs = [];

//    public $superCells;   10
//    public $pelletCells;  1
//    public $emptyCells;   0
//    public $unknownCells; 0.5
    public $pellets = [];

    /**
     * @var array
     */
    public $weights;

    public function __clone()
    {
        $this->myPacs = array_map(function ($pac) { return clone $pac; }, $this->myPacs);
    }
}