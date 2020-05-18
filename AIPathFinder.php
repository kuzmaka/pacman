<?php

class AIPathFinder
{
    private $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function findGreedyPaths(State $state)
    {
        $start = microtime(true);

        // 1. BFS* for each pac
//        $state = clone $state;  // to not spoil pacs
//        echo "cloned state in: " . (1000 * (microtime(true) - $start)) . "ms\n";

        $map = $this->game->map;
        $pellets = $state->pellets;
        foreach ($state->myPacs as $pac) {
            $cell = $map->cell($pac->x, $pac->y);
            $pac->q = [[$cell, 0]];
            $pac->visited = [];
            $pac->parents = [];
            $pac->path = [];
        }
        $visitedPellets = [];
        $inSomePath = [];
        $ops = [];
        foreach ($state->opPacs as $opPac) {
            $ops["$opPac->x $opPac->y"] = 1;
        }
        $once = true;
        for ($step = 0; $step < 9999 && $once; $step++) {
            $once = false;
            foreach ($state->myPacs as $pac) {
                while ($pac->q && reset($pac->q)[1] == $step) {
                    $once = true;
                    list($cell, $d) = array_shift($pac->q);
                    if (!isset($cell->distance[$pac->id])) {
                        $cell->distance[$pac->id] = $d;
                    }

                    $xy = "$cell->x $cell->y";

                    // if it's a pellet not met before
                    if (isset($pellets[$xy]) && !isset($visitedPellets[$xy])) {
                        // restore path
                        $partialPath = [$cell];
                        $_xy = $xy;
                        $inThisPath = [];
                        $inThisPath[$_xy . ' ' . $cell->distance[$pac->id]] = 1;
                        while (isset($pac->parents[$_xy])) {
                            $parent = $pac->parents[$_xy];
                            $partialPath[] = $parent;
                            $_xy = "$parent->x $parent->y";
                            $inThisPath[$_xy . ' ' . $parent->distance[$pac->id]] = 1;
                        }
                        array_pop($partialPath);
//                        error_log('A' . var_export($inSomePath, true));
                        array_pop($inThisPath);
//                        error_log('B' . var_export($inSomePath, true));
                        // check intersections
                        foreach ($inThisPath as $key => $_) {
                            if (isset($inSomePath[$key])) {
                                goto _abort;
                            }
                        }
                        foreach ($inThisPath as $key => $_) {
                            $inSomePath[$key] = 1;
                        }

                        $pac->path = array_merge($pac->path, array_reverse($partialPath));

                        // restart BFS from here
                        $pac->q = [];
                        $pac->visited = [];
                        $pac->parents = [];
                        $visitedPellets[$xy] = 1;

                        // make path fixed

                        // make other pacs to respect path

                        // check if it's enough
                        $enough = true;
                        foreach ($state->myPacs as $p) {
                            if (!isset($p->path) || count($p->path) < 2) {
                                $enough = false;
                            }
                        }
                        if ($enough) {
                            break 3;
                        }
                    }
_abort:
                    $pac->visited[$xy] = 1;

                    $nextCells = $cell->nextCells;
                    // reorder according to weights
                    usort($nextCells, function ($c1, $c2) use ($state, $pac) {
                        $w1 = $state->weights[$pac->id]["$c1->x $c1->y"] ?? 0;
                        $w2 = $state->weights[$pac->id]["$c2->x $c2->y"] ?? 0;
                        if ($w1 == $w2) {
                            return 0;
                        }
                        return $w1 < $w2 ? 1 : -1;
                    });
                    foreach ($nextCells as $nextCell) {
                        if (isset($pac->visited["$nextCell->x $nextCell->y"])) {
                            continue;
                        }
//                        error_log("check inSomePath pac $pac->id: " . "$nextCell->x $nextCell->y " . ($d + 1));
                        if (isset($inSomePath["$nextCell->x $nextCell->y " . ($d + 1)])) {
                            continue;
                        }
                        if ($step < 2 && isset($ops["$nextCell->x $nextCell->y"])) {
                            continue;
                        }
                        $pac->q[] = [$nextCell, $d + 1];
                        $pac->parents["$nextCell->x $nextCell->y"] = $cell;
                    }
                }
            }
        }
//        echo "total steps: $step\n";
        error_log("total time: " . (1000 * (microtime(true) - $start)) . "ms");


        // dump $map for pacs
        foreach ($map->cells as $cell) {
            $cell->v = null;
        }

        foreach ($state->myPacs as $pac) {
            $s = "PAC $pac->id ($pac->x, $pac->y) path: ";
            foreach ($pac->path as $cell) {
                $s .= "($cell->x, $cell->y) ";
//                $cell->v[$pac->id] = chr(ord('A') + $pac->id);
            }
            error_log($s);
        }

//        $s = '';
//        for ($y = 0; $y < $map->h; $y++) {
//            for ($x = 0; $x < $map->w; $x++) {
//                if ($cell = $map->cellAt($x, $y)) {
//                    foreach ($state->myPacs as $pac) {
//                        $v = $cell->v[$pac->id] ?? ' ';
////                        $v = $cell->distance[$pac->id] ?? ' ';
////                        $s .= str_pad($v < 10 ? $v : '*', 1, ' ', STR_PAD_LEFT);
////                        $s .= $v !== ' ' ? $v !== 0 ? dechex($v%16) : '0' : ' ';
//                        $s .= $v;
//                    }
//                } else {
//                    $s .= str_repeat('#', count($state->myPacs));
//                }
//            }
//            $s .= "\n";
//        }
//        echo $s;
//        echo "\n";
//die;


//        $paths = [];
//        foreach ($state->myPacs as $pac) {
//            $paths[$pac->id] = $pac->path;
//        }
//        return $paths;
    }

//    public function getNextTurn(State $state)
//    {
//        $paths = $this->findGreedyPaths($state);
//        $this->game->getPossibleTurns();
//    }
}