<?php

class Game
{
    private $ai;
    private $state;
//    private $states;
    private $superCells = [];

    /**
     * @var Map
     */
    public $map;

    public function __construct()
    {
        $this->ai = new AIPathFinder($this);
        $this->state = new State();
//        $this->states = [];

    }

    public function getActions(InputState $inputState, $first)
    {
        $this->updateState($inputState, $first);

        $this->ai->findGreedyPaths($this->state);

        $actions = [];
        foreach ($this->state->myPacs as $pac) {
            if ($pac->abilityCooldown == 0) {
                $actions[] = "SPEED $pac->id";
            } else {
                if ($pac->speedTurnsLeft > 0) {
                    $cell = $this->map->cell($pac->x, $pac->y);
                    $cell1 = reset($pac->path);
                    if ($cell1) {
                        $cell2 = next($pac->path);
                        if (!$cell2 || $cell === $cell2) {
                            $actions[] = "MOVE $pac->id $cell1->x $cell1->y " . ($this->state->weights[$pac->id]["$cell1->x $cell1->y"] ?? 0);
                        } else {
                            $actions[] = "MOVE $pac->id $cell2->x $cell2->y " . ($this->state->weights[$pac->id]["$cell2->x $cell2->y"] ?? 0);
                        }
                    }
                } else {
                    $cell = reset($pac->path);
                    if ($cell) {
                        $actions[] = "MOVE $pac->id $cell->x $cell->y " . ($this->state->weights[$pac->id]["$cell->x $cell->y"] ?? 0);
                    }
                }
            }
        }

        return $actions;
    }

    public function initState(InputMap $inputMap)
    {
        for ($y = 0; $y < $inputMap->height; $y++) {
            for ($x = 0; $x < $inputMap->width; $x++) {
                if ($inputMap->rows[$y][$x] == ' ') {
                    $this->state->pellets["$x $y"] = 1;
                }
            }
        }
        $this->map = new Map($inputMap->width, $inputMap->height, $inputMap->rows);
    }

    public function updateState(InputState $inputState, $first)
    {
//        $this->state->i++;

        $this->state->myScore = $inputState->myScore;

        $this->state->myPacs = [];
        $this->state->opPacs = [];
        foreach ($inputState->pacs as $statePac) {
            if ($statePac->mine && $statePac->typeId != 'DEAD') {
                $stateMyPac = new StateMyPac();
                $stateMyPac->id = $statePac->pacId;
                $stateMyPac->x = $statePac->x;
                $stateMyPac->y = $statePac->y;
                $stateMyPac->speedTurnsLeft = $statePac->speedTurnsLeft;
                $stateMyPac->abilityCooldown = $statePac->abilityCooldown;
                $this->state->myPacs[] = $stateMyPac;
            } elseif (!$statePac->mine && $statePac->typeId != 'DEAD') {
                $stateOpPac = new StateMyPac();
                $stateOpPac->id = $statePac->pacId;
                $stateOpPac->x = $statePac->x;
                $stateOpPac->y = $statePac->y;
                $stateOpPac->speedTurnsLeft = $statePac->speedTurnsLeft;
                $stateOpPac->abilityCooldown = $statePac->abilityCooldown;
                $this->state->opPacs[] = $stateOpPac;
            }
        }

        // remove pellets where pacs are standing
        foreach ($inputState->pacs as $statePac) {
            if ($statePac->typeId != 'DEAD') {
                unset($this->state->pellets["$statePac->x $statePac->y"]);
            }
        }

        // remove pellets which became invisible
        $visibleCells = [];
        foreach ($inputState->pacs as $statePac) {
            $cell = $this->map->cell($statePac->x, $statePac->y);
            $visibleCells = array_merge($visibleCells, $cell->getVisibleCells());
        }
        $liveSuperCells = [];
        $liveVisibleCells = [];
        foreach ($inputState->pellets as $xy => $v) {
            list($x, $y) = explode(' ', $xy);

            if ($first) {
                if ($v == 10) {
                    $this->superCells[] = $this->map->cell($x, $y);
                }
            }
            if ($v == 10) {
                $liveSuperCells[] = $this->map->cell($x, $y);
            }

            $liveVisibleCells[] = $this->map->cell($x, $y);

            $this->state->pellets[$xy] = $v;
        }
        foreach ($this->superCells as $superCell) {
            if (!in_array($superCell, $liveSuperCells, true)) {
                unset($this->state->pellets["$superCell->x $superCell->y"]);
            }
        }
        $this->superCells = $liveSuperCells;
        foreach ($visibleCells as $visibleCell) {
            if (!in_array($visibleCell, $liveVisibleCells, true)) {
                unset($this->state->pellets["$visibleCell->x $visibleCell->y"]);
            }
        }

        // let live super pellets shine brightly!
        $this->state->weights = [];
        if ($liveSuperCells) {
            $pacSuperCell = [];
            foreach ($this->state->myPacs as $pac) {
                $q = [[$this->map->cell($pac->x, $pac->y), 0]];
                $visited = [];
                while ($q) {
                    list($cell, $d) = array_shift($q);
                    foreach ($liveSuperCells as $superCell) {
                        if ($cell->x == $superCell->x && $cell->y == $superCell->y) {
                            $pacSuperCell[$pac->id]["$superCell->x $superCell->y"] = [$pac, $superCell, $d];
                            if (count($pacSuperCell[$pac->id]) == count($liveSuperCells)) {
                                break 2;
                            }
                        }
                    }
                    $visited["$cell->x $cell->y"] = 1;
                    foreach ($cell->nextCells as $nextCell) {
                        if (!isset($visited["$nextCell->x $nextCell->y"])) {
                            $q[] = [$nextCell, $d + 1];
                        }
                    }
                }
                error_log("Pac $pac->id, pac super cells " . count($pacSuperCell[$pac->id]) . ', live super cells ' . count($liveSuperCells));
                foreach ($pacSuperCell[$pac->id] as $xy => list(,,$d)) {
                    error_log("($xy) $d");
                }
            }

            $pairs = [];
//            $pacs = $this->state->myPacs;
//            $cells = $liveSuperCells;
//            while (count($pairs) < min(count($this->state->myPacs), count($liveSuperCells))) {
            while ($pacSuperCell && count($pairs) < min(count($this->state->myPacs), count($liveSuperCells))) {
                $min = 999999;
                $minPac = null;
                $minSuperCell = null;
                foreach ($this->state->myPacs as $pac) {
                    foreach ($liveSuperCells as $superCell) {
                        if (isset($pacSuperCell[$pac->id]["$superCell->x $superCell->y"])) {
                            list($pac, $superCell, $d) = $pacSuperCell[$pac->id]["$superCell->x $superCell->y"];

                            if ($d < $min) {
                                $min = $d;
                                $minPac = $pac;
                                $minSuperCell = $superCell;
                            }
                        }
                    }
                }
                error_log("$min $minPac->id ($minSuperCell->x, $minSuperCell->y)");
                $pairs[] = [$minPac, $minSuperCell];
                foreach ($this->state->myPacs as $pac) {
                    unset($pacSuperCell[$pac->id]["$minSuperCell->x $minSuperCell->y"]);
                }
                unset($pacSuperCell[$minPac->id]);
            }

            foreach ($pairs as list($pac, $superCell)) {
                $this->state->weights[$pac->id] = [];
                $q = [[$superCell, 0]];
                $visited = [];
                while ($q) {
                    list($cell, $d) = array_shift($q);
                    $this->state->weights[$pac->id]["$cell->x $cell->y"] = 1 / ($d + 1);
                    $visited["$cell->x $cell->y"] = 1;
                    foreach ($cell->nextCells as $nextCell) {
                        if (isset($visited["$nextCell->x $nextCell->y"])) {
                            continue;
                        }
                        $q[] = [$nextCell, $d + 1];
                    }
                }
            }
        }
    }

    public function getPossibleTurns(State $state)
    {
        foreach ($state->myPacs as $pac) {
            $actions[$pac->id] = [];
            if ($pac->abilityCooldown == 0) {
                $actions[$pac->id][] = "SPEED $pac->id";
            } else {
                if ($pac->speedTurnsLeft == 0) {
                    $cell = $this->map->cell($pac->x, $pac->y);
                    foreach ($cell->nextCells as $nextCell) {
                        $actions[$pac->id][] = "MOVE $pac->id $nextCell->x $nextCell->y";
                    }
                } else {
                    $cell = $this->map->cellAt($pac->x, $pac->y);
                    foreach ($cell->nextCells as $nextCell) {
                        foreach ($nextCell->nextCells as $nextNextCell) {
                            if ($nextNextCell->x != $cell->x || $nextNextCell->y != $cell->y) {
                                $actions[$pac->id][] = "MOVE $pac->id $nextNextCell->x $nextNextCell->y $nextCell->x,$nextCell->y";
                            }
                        }
                    }
                }
            }
            if (!$actions[$pac->id]) {
                unset($actions[$pac->id]);
            }
        }

        // combine turns from all pacs
        $turns = [];
        $pacActions = array_shift($actions);
        foreach ($pacActions as $pacAction) {
            $turns[] = [$pacAction];
        }
        while ($pacActions = array_shift($actions)) {
            $newTurns = [];
            foreach ($turns as $turn) {
                foreach ($pacActions as $pacAction) {
                    $newTurn = $turn;
                    $newTurn[] = $pacAction;
                    $newTurns[] = $newTurn;
                }
            }
            $turns = $newTurns;
        }

//        error_log('getPossibleTurns ' . round(1000000 * (microtime(true) - $start)));

        return $turns;
    }

    public function nextState(State $state, $turn)
    {
        $start = microtime(true);

        $nextState = clone $state;

        error_log("from state: $state->key; turn " . implode('|', $turn));

        $nextState->i++;

        foreach ($turn as $action) {
            @list($act, $pacId, $x, $y, $p) = explode(' ', $action);
            $pac = null;
            foreach ($nextState->myPacs as $myPac) {
                if ($myPac->id == $pacId) {
                    $pac = $myPac;
                    break;
                }
            }
            if ($act == 'SPEED') {
                $pac->speedTurnsLeft = 5;
                $pac->abilityCooldown = 10;
            } elseif ($act == 'MOVE') {
                if ($pac->speedTurnsLeft == 0) {
                    if (isset($nextState->pellets["$x $y"])) {
                        $nextState->myScore += $nextState->pellets["$x $y"];
                        unset($nextState->pellets["$x $y"]);
                    }
                    $pac->x = $x;
                    $pac->y = $y;
                } else {
                    if (isset($nextState->pellets["$x $y"])) {
                        $nextState->myScore += $nextState->pellets["$x $y"];
                        unset($nextState->pellets["$x $y"]);
                    }
                    list($u, $v) = explode(',', $p);
                    if (isset($nextState->pellets["$u $v"])) {
                        $nextState->myScore += $nextState->pellets["$u $v"];
                        unset($nextState->pellets["$u $v"]);
                    }
                    $pac->x = $x;
                    $pac->y = $y;
                }
            }
            if ($pac->speedTurnsLeft > 0) {
                $pac->speedTurnsLeft--;
            }
            if ($pac->abilityCooldown > 0) {
                $pac->abilityCooldown--;
            }
        }

        $nextState->key = $this->dumpState($nextState);

        error_log("to state: $nextState->key");

//        error_log('nextState ' . round(1000000 * (microtime(true) - $start)));

        return $nextState;
    }

    public function score(State $state)
    {
//        error_log('score start');
//        $start = microtime(true);

        // BFS, score with time discount
        $score = $state->myScore;

        /*
        $q = [];
        foreach ($state->myPacs as $pac) {
            $x = $pac->x;
            $y = $pac->y;
            $q[$pac->id][] = [$this->map->cellAt($x, $y), 0];
            $visitedCells[$pac->id] = [];
        }
        $allVisitedCells = [];
        $notEmpty = true;
        while ($notEmpty) {
            $notEmpty = false;
            foreach ($state->myPacs as $pac) {
                if (!$q[$pac->id]) {
                    continue;
                }
                $notEmpty = true;
                list($cell, $dist) = array_shift($q[$pac->id]);
                $visitedCells[$pac->id][] = $cell;
                $allVisitedCells[] = $cell;
                if ($dist && isset($state->pellets["$cell->x $cell->y"]) && !in_array($cell, $allVisitedCells, true)) {
                    $score += $state->pellets["$cell->x $cell->y"] / ($dist + 1);
                }
                foreach ($cell->nextCells as $nextCell) {
                    if (!in_array($nextCell, $visitedCells[$pac->id], true)) {
                        $q[$pac->id][] = [$nextCell, $dist + 1];
                    }
                }
                if ($dist > 50) {
                    $q[$pac->id] = [];
                }
            }
        }

        error_log('score end ' . (1000 * (microtime(true) - $start)));
        */

        return $score;
    }

    public function dumpState(State $state)
    {
        $s = "$state->i $state->myScore ";
        foreach ($state->myPacs as $pac) {
            $s .= "($pac->id $pac->x $pac->y) ";
        }
        return $s;
    }
}
