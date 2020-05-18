<?php

class AIRandom
{
    public function getNextTurn(State $state)
    {
        $actions = [];
        foreach ($state->myPacs as $pac) {
            $dirs = [[-1, 0], [1, 0], [0, 1], [0, -1]];
            shuffle($dirs);
            foreach ($dirs as $dir) {
                list($dx, $dy) = $dir;
                $x = $pac->x + $dx;
                $y = $pac->y + $dy;
                if (isset($state->pellets["$x $y"])) {
                    $actions[] = "MOVE $pac->id $x $y *";
                    break;
                }
            }
        }
        return $actions;
    }
}