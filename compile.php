<?php


append_file('main.php');
append_file('InputMap.php');
append_file('InputState.php');
append_file('InputStatePac.php');
append_file('Game.php');
append_file('State.php');
append_file('StateMyPac.php');
append_file('AIPathFinder.php');
append_file('Map.php');
append_file('Cell.php');

function append_file($file)
{
    static $first = true;

    $s = file_get_contents($file);
    $s = preg_replace('/^require_once.*$/m', '', $s);
    if (!$first) {
        echo "\n\n////////////////// $file ////////////////////\n\n";
        $s = preg_replace('/^<\?(php)?/', '', $s, 1);
    }
    echo $s;

    $first = false;
}