<?php

$game = new Game();

fscanf(STDIN, "%d %d", $width, $height);
error_log(sprintf("%d %d", $width, $height));
for ($i = 0; $i < $height; $i++) {
    $rows[] = str_pad(stream_get_line(STDIN, $width + 1, "\n"), $width);
    error_log($rows[$i]);
}
$inputMap = new InputMap($width, $height, $rows);
$game->initState($inputMap);

$first = true;
while (true) {
    fscanf(STDIN, "%d %d", $myScore, $opponentScore);
    error_log(sprintf("%d %d", $myScore, $opponentScore));
    if ($myScore < 0) {
        break;
    }

    $inputState = new InputState($myScore, $opponentScore);
    fscanf(STDIN, "%d", $visiblePacCount);
    error_log(sprintf("%d", $visiblePacCount));
    for ($i = 0; $i < $visiblePacCount; $i++)
    {
        fscanf(STDIN, "%d %d %d %d %s %d %d", $pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown);
        error_log(sprintf("%d %d %d %d %s %d %d", $pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown));
        $inputState->pacs[] = new InputStatePac($pacId, $mine, $x, $y, $typeId, $speedTurnsLeft, $abilityCooldown);
    }

    fscanf(STDIN, "%d", $visiblePelletCount);
    error_log(sprintf("%d", $visiblePelletCount));
    for ($i = 0; $i < $visiblePelletCount; $i++) {
        fscanf(STDIN, "%d %d %d", $x, $y, $value);
        error_log(sprintf("%d %d %d", $x, $y, $value));
        $inputState->pellets["$x $y"] = $value;
    }

    $actions = $game->getActions($inputState, $first);
    echo implode('|', $actions), "\n";

    $first = false;
}
