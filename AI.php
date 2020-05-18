<?php

interface AI
{
    public function getNextTurn(State $state);
}