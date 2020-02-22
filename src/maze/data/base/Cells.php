<?php

namespace VovanVE\MazeProject\maze\data\base;

use VovanVE\MazeProject\maze\data\Cell;

abstract class Cells
{
    /** @var Cell[] */
    protected $cells = [];

    public function isEmpty(): bool
    {
        return [] === $this->cells;
    }

    public function has(Cell $cell): bool
    {
        return $this->hasKey($this->getKey($cell));
    }

    protected function hasKey(string $key): bool
    {
        return isset($this->cells[$key]);
    }

    protected function getKey(Cell $cell): string
    {
        return "{$cell->getX()};{$cell->getY()}";
    }
}
