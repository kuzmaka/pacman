<?php

class Pac
{
    const ROCK = 'ROCK';
    const PAPER = 'PAPER';
    const SCISSORS = 'SCISSORS';
//    const WEAKER = [self::ROCK => self::PAPER, self::PAPER => self::SCISSORS, self::SCISSORS => self::ROCK];
    const STRONGER = [self::ROCK => self::PAPER, self::PAPER => self::SCISSORS, self::SCISSORS => self::ROCK];

    public $id;
    public $cell;
    public $type;
    public $speedTurnsLeft;
    public $abilityCooldown;

    public function __construct($id, Cell $cell, $type, $speedTurnsLeft, $abilityCooldown)
    {
        $this->id = $id;
        $this->cell = $cell;
        $this->type = $type;
        $this->speedTurnsLeft = $speedTurnsLeft;
        $this->abilityCooldown = $abilityCooldown;
    }

    public function getVisibleCells()
    {
        return $this->cell->getVisibleCells();
    }

    public function getNearestPellet(array $busyCells, $mindist = false)
    {
        $q = [[$this->cell, 0]];
        $visited = [];
        $all = [];
        while ($q) {
            list($cell, $d) = array_shift($q);
            if ($cell->v > 0 && !in_array($cell, $busyCells, true)) {
                $all[] = [$cell, $d];
//                return $cell;
            }
            $visited[] = $cell;
            foreach ($cell->nextCells as $nextCell) {
                if (!in_array($nextCell, $visited, true)) {
                    $q[] = [$nextCell, $d + 1];
                }
            }
        }

        usort($all, function ($a, $b) use ($mindist) {
            list($cell1, $d1) = $a;
            list($cell2, $d2) = $b;
            $v1 = $cell1->v - ($mindist && $d1 == 1 ? 3 : $d1);
            $v2 = $cell2->v - ($mindist && $d2 == 1 ? 3 : $d2);
            if ($v1 == $v2) {
                return 0;
            }
            return $v1 < $v2 ? 1 : -1;
        });

        if (!$all) {
            return null;
        }

        return reset($all)[0];
    }

    /**
     * @param Pac[] $enemies
     */
    public function getNearestEnemy($enemies)
    {
        foreach ($this->cell->nextCells as $nextCell) {
            foreach ($enemies as $enemy) {
                if ($enemy->cell === $nextCell) {
                    return $enemy;
                }
            }
            foreach ($nextCell->nextCells as $nextNextCell) {
                foreach ($enemies as $enemy) {
                    if ($enemy->cell === $nextNextCell) {
                        return $enemy;
                    }
                }
            }
        }
        return null;
    }

//    public function getStrongerType(Pac $pac)
//    {
//        return self::LESS[$pac->type] === $this->type;
//    }
}