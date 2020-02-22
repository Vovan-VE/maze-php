<?php

namespace VovanVE\MazeProject\tests\helpers;

use VovanVE\MazeProject\maze\data\base\Cells;
use VovanVE\MazeProject\maze\data\Cell;

class BaseCells extends Cells
{
    public function setCells(Cell ...$cells): void
    {
        $map = [];
        foreach ($cells as $cell) {
            $map[$this->getKey($cell)] = $cell;
        }
        $this->cells = $map;
    }
}
