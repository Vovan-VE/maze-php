<?php

namespace VovanVE\MazeProject\maze\data;

use VovanVE\MazeProject\maze\data\base\Cells;

class CellsUniqStack extends Cells
{
    /**
     * @return \Generator|Cell[]
     */
    public function iterate(): \Generator
    {
        foreach ($this->cells as $cell) {
            yield $cell;
        }
    }

    public function push(Cell $cell): void
    {
        $key = $this->getKey($cell);
        if ($this->hasKey($key)) {
            throw new \RuntimeException(
                'The same cell is already in stack'
            );
        }
        $this->cells[$key] = $cell;
    }

    public function pop(): Cell
    {
        if ([] === $this->cells) {
            throw new \UnderflowException('The stack is empty');
        }

        end($this->cells);
        $key = key($this->cells);
        $cell = $this->cells[$key];
        unset($this->cells[$key]);
        return $cell;
    }

    public function popUntil(Cell $cell): void
    {
        $key = $this->getKey($cell);
        if (!$this->hasKey($key)) {
            throw new \InvalidArgumentException('Cell is not in stack');
        }

        end($this->cells);
        while ($this->cells && $key !== ($lastKey = key($this->cells))) {
            unset($this->cells[$lastKey]);
            end($this->cells);
        }
    }

    public function isPrevious(Cell $cell): bool
    {
        if (count($this->cells) < 2) {
            return false;
        }
        $key = $this->getKey($cell);

        $pair = array_slice($this->cells, -2, 1, true);
        reset($pair);
        return key($pair) === $key;
    }
}
