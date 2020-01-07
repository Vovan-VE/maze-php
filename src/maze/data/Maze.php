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
    /** @var DoorPosition|null */
    private $in;
    /** @var DoorPosition|null */
    private $out;

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

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return DoorPosition|null
     */
    public function getEntrance(): ?DoorPosition
    {
        return $this->in;
    }

    /**
     * @return DoorPosition|null
     */
    public function getExit(): ?DoorPosition
    {
        return $this->out;
    }

    /**
     * @return Cell|null
     * @deprecated Unused yet
     */
    public function getEntranceCell(): ?Cell
    {
        return $this->getDoorCell($this->in);
    }

    /**
     * @return Cell|null
     * @deprecated Unused yet
     */
    public function getExitCell(): ?Cell
    {
        return $this->getDoorCell($this->out);
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

    public function setEntrance(int $side, int $offset): void
    {
        if (
            $this->out &&
            $this->out->getOuterWallSide() === $side &&
            $this->out->getCellIndex() === $offset
        ) {
            throw new \LogicException('This place is already assigned to Exit');
        }

        [$x, $y] = $this->getDoorCoords($side, $offset);

        $this->removeWalls($x, $y, $side, true);
        $this->in = new DoorPosition($side, $offset);
    }

    public function setExit(int $side, int $offset): void
    {
        if (
            $this->in &&
            $this->in->getOuterWallSide() === $side &&
            $this->in->getCellIndex() === $offset
        ) {
            throw new \LogicException(
                'This place is already assigned to Entrance'
            );
        }

        [$x, $y] = $this->getDoorCoords($side, $offset);

        $this->removeWalls($x, $y, $side, true);
        $this->out = new DoorPosition($side, $offset);
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

    /**
     * @param int $side
     * @param int $offset
     * @return int[]
     */
    private function getDoorCoords(int $side, int $offset): array
    {
        switch ($side) {
            case Direction::TOP:
                return [$offset, 0];

            case Direction::RIGHT:
                return [$this->width - 1, $offset];

            case Direction::BOTTOM:
                return [$offset, $this->height - 1];

            case Direction::LEFT:
                return [0, $offset];

            default:
                throw new \InvalidArgumentException('Invalid direction');
        }
    }

    /**
     * @param DoorPosition|null $door
     * @return Cell|null
     */
    private function getDoorCell(?DoorPosition $door): ?Cell
    {
        if (!$door) {
            return null;
        }
        [$x, $y] = $this->getDoorCoords(
            $door->getOuterWallSide(),
            $door->getCellIndex()
        );
        return $this->getCell($x, $y);
    }
}
