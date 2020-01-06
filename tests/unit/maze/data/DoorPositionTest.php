<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class DoorPositionTest extends BaseTestCase
{
    public function testCreate()
    {
        $pos = new DoorPosition(Direction::LEFT, 42);

        $this->assertEquals(Direction::LEFT, $pos->getOuterWallSide());
        $this->assertEquals(42, $pos->getCellIndex());
    }
}
