<?php

class InputState
{
    public $myScore;
    public $opponentScore;

    /**
     * @var InputStatePac[]
     */
    public $pacs = [];

    public $pellets = [];

    public function __construct($myScore, $opponentScore)
    {
        $this->myScore = $myScore;
        $this->opponentScore = $opponentScore;
    }
}