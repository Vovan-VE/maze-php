<?php

namespace VovanVE\MazeProject\maze\data;

class DoorPosition
{
    /** @var int */
    private $outerWallSide;
    /** @var int */
    private $cellIndex;

    public function __construct(int $outerWallSide, int $cellIndex)
    {
        $this->outerWallSide = $outerWallSide;
        $this->cellIndex = $cellIndex;
    }

    /**
     * @return int
     */
    public function getOuterWallSide(): int
    {
        return $this->outerWallSide;
    }

    /**
     * @return int
     */
    public function getCellIndex(): int
    {
        return $this->cellIndex;
    }
}
