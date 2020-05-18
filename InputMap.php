<?php

class InputMap
{
    public $width;
    public $height;
    public $rows;

    public function __construct($width, $height, $rows)
    {
        $this->width = $width;
        $this->height = $height;
        $this->rows = $rows;
    }
}