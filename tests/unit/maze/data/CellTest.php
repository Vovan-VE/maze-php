<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class CellTest extends BaseTestCase
{
    public function testConstruct()
    {
        $cell = new Cell(42, 37);
        $this->assertEquals(42, $cell->getX());
        $this->assertEquals(37, $cell->getY());
        $this->assertTrue($cell->topWall);
        $this->assertTrue($cell->rightWall);
        $this->assertTrue($cell->bottomWall);
        $this->assertTrue($cell->leftWall);

        $cell = new Cell(0, 0, false);
        $this->assertEquals(0, $cell->getX());
        $this->assertEquals(0, $cell->getY());
        $this->assertFalse($cell->topWall);
        $this->assertFalse($cell->rightWall);
        $this->assertFalse($cell->bottomWall);
        $this->assertFalse($cell->leftWall);
    }

    public function testSetWallAt()
    {
        $cell = new Cell(42, 37);

        $cell->setWallAt(Direction::TOP, false);
        $this->assertFalse($cell->topWall);
        $this->assertTrue($cell->rightWall);
        $this->assertTrue($cell->bottomWall);
        $this->assertTrue($cell->leftWall);

        $cell->setWallAt(Direction::RIGHT, false);
        $this->assertFalse($cell->topWall);
        $this->assertFalse($cell->rightWall);
        $this->assertTrue($cell->bottomWall);
        $this->assertTrue($cell->leftWall);

        $cell->setWallAt(Direction::BOTTOM, false);
        $this->assertFalse($cell->topWall);
        $this->assertFalse($cell->rightWall);
        $this->assertFalse($cell->bottomWall);
        $this->assertTrue($cell->leftWall);

        $cell->setWallAt(Direction::LEFT, false);
        $this->assertFalse($cell->topWall);
        $this->assertFalse($cell->rightWall);
        $this->assertFalse($cell->bottomWall);
        $this->assertFalse($cell->leftWall);

        $cell->setWallAt(Direction::TOP, true);
        $this->assertTrue($cell->topWall);
        $this->assertFalse($cell->rightWall);
        $this->assertFalse($cell->bottomWall);
        $this->assertFalse($cell->leftWall);

        $cell->setWallAt(Direction::RIGHT, true);
        $this->assertTrue($cell->topWall);
        $this->assertTrue($cell->rightWall);
        $this->assertFalse($cell->bottomWall);
        $this->assertFalse($cell->leftWall);

        $cell->setWallAt(Direction::BOTTOM, true);
        $this->assertTrue($cell->topWall);
        $this->assertTrue($cell->rightWall);
        $this->assertTrue($cell->bottomWall);
        $this->assertFalse($cell->leftWall);

        $cell->setWallAt(Direction::LEFT, true);
        $this->assertTrue($cell->topWall);
        $this->assertTrue($cell->rightWall);
        $this->assertTrue($cell->bottomWall);
        $this->assertTrue($cell->leftWall);
    }
}
