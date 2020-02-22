<?php

namespace VovanVE\MazeProject\maze\data;

class Cell
{
    /** @var bool */
    public $topWall;
    /** @var bool */
    public $rightWall;
    /** @var bool */
    public $bottomWall;
    /** @var bool */
    public $leftWall;

    /** @var int */
    private $x;
    /** @var int */
    private $y;

    public function __construct(int $x, int $y, bool $allWalls = true)
    {
        $this->x = $x;
        $this->y = $y;

        $this->topWall = $allWalls;
        $this->rightWall = $allWalls;
        $this->bottomWall = $allWalls;
        $this->leftWall = $allWalls;
    }

    /**
     * @return int
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    public function isSameCoords(Cell $to): bool
    {
        return (
            $this === $to ||
            $this->x === $to->getX() && $this->y === $to->getY()
        );
    }

    public function hasWallAt(int $direction): bool
    {
        switch ($direction) {
            case Direction::TOP:
                return $this->topWall;

            case Direction::RIGHT:
                return $this->rightWall;

            case Direction::BOTTOM:
                return $this->bottomWall;

            case Direction::LEFT:
                return $this->leftWall;
        }
        return false;
    }

    public function setWallAt(int $direction, bool $wall): void
    {
        switch ($direction) {
            case Direction::TOP:
                $this->topWall = $wall;
                break;

            case Direction::RIGHT:
                $this->rightWall = $wall;
                break;

            case Direction::BOTTOM:
                $this->bottomWall = $wall;
                break;

            case Direction::LEFT:
                $this->leftWall = $wall;
                break;
        }
    }
}
