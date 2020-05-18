<?php

class AIMonteCarlo
{
    const TIME_LIMIT = 40;
    const TURNS_LIMIT = 1;

    private $game;
    private $stats = [];

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @param State[] $states
     * @return array
     */
    public function getNextTurn($states)
    {
        $games = 0;
        $start = microtime(true);
        while (1000 * (microtime(true) - $start) < self::TIME_LIMIT) {
            $this->simulate($states);
            $games++;
        }
        error_log("$games games simulated in " . round(1000 * (microtime(true) - $start)) . " ms");

//        foreach ($this->stats as $key => $stat) {
//            echo "$key: $stat->score / $stat->plays\n";
//        }
//        die;

        $possibleTurns = $this->game->getPossibleTurns($states);
        error_log('for ' . count($possibleTurns) . ' possible turns');
        $maxAvgScore = -1;
        $bestTurn = null;
        $state = end($states);
        foreach ($possibleTurns as $turn) {
            $nextState = $this->game->nextState($state, $turn);
            if (isset($this->stats[$nextState->key])) {
                $stat = $this->stats[$nextState->key];
                $avgScore = $stat->score / $stat->plays;
                if ($avgScore > $maxAvgScore) {
                    $maxAvgScore = $avgScore;
                    $bestTurn = $turn;
                }
//                error_log("$maxAvgScore >< $avgScore ($stat->score / $stat->plays)");
//                echo("$maxAvgScore >< $avgScore ($stat->score / $stat->plays)\n");
            }
        }

        return $bestTurn;
    }

    /**
     * @param State[] $states
     */
    public function simulate($states)
    {
//        echo "simulate $state->key\n";

        $visitedStatKeys = [];
        $expand = true;
        $state = end($states);
//        for ($i = 0; $i < self::TURNS_LIMIT; $i++) {
        for ($i = 0; $i < 1; $i++) {
            $possibleTurns = $this->game->getPossibleTurns($states);

            $possibleStates = [];
            $all = true;
            foreach ($possibleTurns as $turn) {
                $newState = $this->game->nextState($state, $turn);
                $possibleStates[] = [$turn, $newState];
                if (!isset($this->stats[$newState->key])) {
                    $all = false;
                    break;
                }
            }
            // $turn $newState
            if (!$all) {
                if ($expand) {
                    $this->stats[$state->key] = (object)[
                        'plays' => 0,
                        'score' => 0
                    ];
                    $visitedStatKeys[] = $state->key;
                    $expand = false;
                }
            }

            $turn = $possibleTurns[array_rand($possibleTurns)];
            $state = $this->game->nextState($state, $turn);
            $states[] = $state;

            if (isset($this->stats[$state->key])) {
                $visitedStatKeys[] = $state->key;
            } elseif ($expand) {
                $this->stats[$state->key] = (object)[
                    'plays' => 0,
                    'score' => 0
                ];
                $visitedStatKeys[] = $state->key;
                $expand = false;
            }
        }
        $score = $this->game->score($state);

        foreach (array_unique($visitedStatKeys) as $key) {
            $this->stats[$key]->plays++;
            $this->stats[$key]->score += $score;
        }
    }
}