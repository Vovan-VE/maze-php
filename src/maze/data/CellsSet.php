<?php

namespace VovanVE\MazeProject\maze\data;

class CellsSet
{
    /** @var Cell[] */
    private $cells = [];

    public function isEmpty(): bool
    {
        return [] === $this->cells;
    }

    public function add(Cell $cell): void
    {
        $key = $this->getKey($cell);
        if (isset($this->cells[$key])) {
            if ($this->cells[$key] !== $cell) {
                throw new \LogicException(
                    'Trying to add different cell with the same coords'
                );
            }
        } else {
            $this->cells[$key] = $cell;
        }
    }

    public function remove(Cell $cell): void
    {
        unset($this->cells[$this->getKey($cell)]);
    }

    public function has(Cell $cell): bool
    {
        return isset($this->cells[$this->getKey($cell)]);
    }

    public function getRandom(): Cell
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException('The Set is empty');
        }

        $count = \count($this->cells);
        $index = \mt_rand(0, $count - 1);
        $slice = \array_slice($this->cells, $index, 1);
        return reset($slice);
    }

    private function getKey(Cell $cell): string
    {
        return "{$cell->getX()};{$cell->getY()}";
    }
}
