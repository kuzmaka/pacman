<?php


class Map
{
    const DIRS = [[-1, 0], [1, 0], [0, 1], [0, -1]];

    public $w;
    public $h;

    /**
     * @var Cell[]
     */
    public $cells;

    public function __construct($w, $h, $rows)
    {
        $this->w = $w;
        $this->h = $h;

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                if ($rows[$y][$x] === ' ') {
                    $this->cells["$x $y"] = new Cell($this, $x, $y);
                }
            }
        }

        foreach ($this->cells as $cell) {
            $cell->initNext();
        }
    }

    public function cellAt($x, $y)
    {
        return $this->cells["$x $y"] ?? null;
    }

    public function cell($x, $y)
    {
        return $this->cells["$x $y"];
    }

    public function sorted($limit)
    {
        $cells = $this->cells;
        usort($cells, function ($a, $b) {
            if ($a->v == $b->v) {
                $ca = $a->y * $this->w + $a->x;
                $cb = $b->y * $this->w + $b->x;
                return $ca < $cb ? 1 : -1;
            }
            return $a->v < $b->v ? 1 : -1;
        });
        return array_slice($cells, 0, $limit);
    }

    public function dump()
    {
        $s = '';
        for ($y = 0; $y < $this->h; $y++) {
            for ($x = 0; $x < $this->w; $x++) {
                if ($cell = $this->cellAt($x, $y)) {
                    $s .= $cell->v < 10 ? $cell->v < 1 ? $cell->v < 0.5 ? ' ' : '?' : '1' : '*';
                } else {
                    $s .= '#';
                }
            }
            $s .= "\n";
        }
        return $s;
    }
}