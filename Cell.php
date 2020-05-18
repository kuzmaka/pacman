<?php

class Cell
{
    public $map;
    public $x;
    public $y;
    public $v;

    /**
     * @var Cell[]
     */
    public $nextCells;

    public function __construct(Map $map, $x, $y)
    {
        $this->map = $map;
        $this->x = $x;
        $this->y = $y;
        $this->v = 1;
        $this->nextCells = [];
    }

    public function initNext()
    {
        foreach (Map::DIRS as list($dx, $dy)) {
            $cell = $this->map->cellAt(
                ($this->map->w + $this->x + $dx) % $this->map->w,
                ($this->map->h + $this->y + $dy) % $this->map->h
            );
            if ($cell) {
                $this->nextCells[] = $cell;
            }
        }
    }

    public function getVisibleCells()
    {
        $cells = [$this];
        foreach (Map::DIRS as list($dx, $dy)) {
            $x = $this->x + $dx;
            $y = $this->y + $dy;
            while ($cell = $this->map->cellAt($x, $y)) {
                $cells[] = $cell;
                $x += $dx;
                $y += $dy;
            }
        }
        return $cells;
    }

    public function dump()
    {
        return "$this->x $this->y $this->v";
    }
}