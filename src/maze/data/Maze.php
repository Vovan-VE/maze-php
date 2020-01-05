<?php

namespace VovanVE\MazeProject\maze\data;

class Maze
{
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var Cell[][] Array of rows, t.i. `cells[y][x]` */
    private $cells;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;

        $this->cells = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
                $row[] = new Cell($x, $y);
            }
            $this->cells[] = $row;
        }
    }

    public function __clone()
    {
        $newCells = [];
        foreach ($this->cells as $row) {
            $newRow = [];
            foreach ($row as $cell) {
                $newRow[] = clone $cell;
            }
            $newCells[] = $newRow;
        }
        $this->cells = $newCells;
    }

    public function getCell(int $x, int $y): Cell
    {
        if ($this->isValidCoords($x, $y)) {
            return $this->cells[$y][$x];
        }

        throw new \OutOfBoundsException("Cell [$x; $y] is out of range");
    }

    /**
     * @return \Generator|Cell[]
     */
    public function getAllCells(): \Generator
    {
        foreach ($this->cells as $row) {
            foreach ($row as $cell) {
                yield $cell;
            }
        }
    }

    public function getAdjacentCell(int $x, int $y, int $direction): ?Cell
    {
        [$nextX, $nextY] = Direction::adjacentCoords($x, $y, $direction);
        if ($this->isValidCoords($nextX, $nextY)) {
            return $this->cells[$nextY][$nextX];
        }
        return null;
    }

    public function removeWalls(
        int $x,
        int $y,
        int $direction,
        bool $isOuter = false
    ): void {
        $current = $this->getCell($x, $y);
        $adjacent = $this->getAdjacentCell($x, $y, $direction);
        if (null !== $adjacent) {
            if ($isOuter) {
                throw new \InvalidArgumentException(
                    'Target cell is not edge cell'
                );
            }

            $adjacent->setWallAt(Direction::opposite($direction), false);
        } else {
            if (!$isOuter) {
                throw new \InvalidArgumentException(
                    'There is no adjacent cell'
                );
            }
        }
        $current->setWallAt($direction, false);
    }

    /**
     * @param int $x
     * @param int $y
     * @return bool
     */
    private function isValidCoords(int $x, int $y): bool
    {
        return $x >= 0 && $y >= 0 && $x < $this->width && $y < $this->height;
    }
}
