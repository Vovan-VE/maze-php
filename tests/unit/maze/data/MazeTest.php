<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class MazeTest extends BaseTestCase
{
    public function testCreate(): Maze
    {
        $maze = new Maze(4, 3);

        $this->assertEquals(4, $maze->getWidth());
        $this->assertEquals(3, $maze->getHeight());

        $actualCoords = [];
        foreach ($maze->getAllCells() as $cell) {
            $x = $cell->getX();
            $y = $cell->getY();
            $actualCoords[] = [$x, $y];

            $this->assertSame($cell, $maze->getCell($x, $y));

            $this->assertTrue($cell->topWall);
            $this->assertTrue($cell->rightWall);
            $this->assertTrue($cell->bottomWall);
            $this->assertTrue($cell->leftWall);
        }

        $this->assertEquals(
            [
                [0, 0],
                [1, 0],
                [2, 0],
                [3, 0],
                [0, 1],
                [1, 1],
                [2, 1],
                [3, 1],
                [0, 2],
                [1, 2],
                [2, 2],
                [3, 2],
            ],
            $actualCoords
        );

        return $maze;
    }

    public function testClone()
    {
        $mazeOrig = new Maze(3, 2);
        /** @var Cell[] $cellsOrig */
        $cellsOrig = \iterator_to_array($mazeOrig->getAllCells());

        $mazeCopy = clone $mazeOrig;
        /** @var Cell[] $cellsCopy */
        $cellsCopy = \iterator_to_array($mazeCopy->getAllCells());
        $this->assertSameSize($cellsOrig, $cellsCopy);
        foreach ($cellsCopy as $i => $cellCopy) {
            $cellOrig = $cellsOrig[$i];
            $this->assertNotSame($cellOrig, $cellCopy);
            $this->assertEquals($cellOrig->getX(), $cellCopy->getX());
            $this->assertEquals($cellOrig->getY(), $cellCopy->getY());
            $this->assertEquals($cellOrig->topWall, $cellCopy->topWall);
            $this->assertEquals($cellOrig->rightWall, $cellCopy->rightWall);
            $this->assertEquals($cellOrig->bottomWall, $cellCopy->bottomWall);
            $this->assertEquals($cellOrig->leftWall, $cellCopy->leftWall);
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @param Maze $maze
     * @dataProvider dataGetCellOutOfRange
     * @depends      testCreate
     */
    public function testGetCellOutOfRange(int $x, int $y, Maze $maze)
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage("Cell [$x; $y] is out of range");
        $maze->getCell($x, $y);
    }

    public function dataGetCellOutOfRange()
    {
        return [
            [0, -1],
            [1, -1],
            [2, -1],
            [3, -1],
            [4, -1],
            [4, 0],
            [4, 1],
            [4, 2],
            [4, 3],
            [3, 3],
            [2, 3],
            [1, 3],
            [0, 3],
            [-1, 3],
            [-1, 2],
            [-1, 1],
            [-1, 0],
            [-1, -1],
        ];
    }

    /**
     * @param Maze $maze
     * @depends testCreate
     */
    public function testGetAdjacentCell(Maze $maze)
    {
        $this->assertNull($maze->getAdjacentCell(0, 0, Direction::TOP));
        $this->assertSame(
            $maze->getCell(1, 0),
            $maze->getAdjacentCell(0, 0, Direction::RIGHT)
        );
        $this->assertSame(
            $maze->getCell(0, 1),
            $maze->getAdjacentCell(0, 0, Direction::BOTTOM)
        );
        $this->assertNull($maze->getAdjacentCell(0, 0, Direction::LEFT));

        $this->assertNull($maze->getAdjacentCell(1, 0, Direction::TOP));
        $this->assertSame(
            $maze->getCell(2, 0),
            $maze->getAdjacentCell(1, 0, Direction::RIGHT)
        );
        $this->assertSame(
            $maze->getCell(1, 1),
            $maze->getAdjacentCell(1, 0, Direction::BOTTOM)
        );
        $this->assertSame(
            $maze->getCell(0, 0),
            $maze->getAdjacentCell(1, 0, Direction::LEFT)
        );

        $this->assertNull($maze->getAdjacentCell(3, 0, Direction::TOP));
        $this->assertNull($maze->getAdjacentCell(3, 0, Direction::RIGHT));
        $this->assertSame(
            $maze->getCell(3, 1),
            $maze->getAdjacentCell(3, 0, Direction::BOTTOM)
        );
        $this->assertSame(
            $maze->getCell(2, 0),
            $maze->getAdjacentCell(3, 0, Direction::LEFT)
        );

        $this->assertSame(
            $maze->getCell(0, 0),
            $maze->getAdjacentCell(0, 1, Direction::TOP)
        );
        $this->assertSame(
            $maze->getCell(1, 1),
            $maze->getAdjacentCell(0, 1, Direction::RIGHT)
        );
        $this->assertSame(
            $maze->getCell(0, 2),
            $maze->getAdjacentCell(0, 1, Direction::BOTTOM)
        );
        $this->assertNull($maze->getAdjacentCell(0, 1, Direction::LEFT));

        $this->assertSame(
            $maze->getCell(0, 1),
            $maze->getAdjacentCell(0, 2, Direction::TOP)
        );
        $this->assertSame(
            $maze->getCell(1, 2),
            $maze->getAdjacentCell(0, 2, Direction::RIGHT)
        );
        $this->assertNull($maze->getAdjacentCell(0, 2, Direction::BOTTOM));
        $this->assertNull($maze->getAdjacentCell(0, 2, Direction::LEFT));

        $this->assertSame(
            $maze->getCell(1, 1),
            $maze->getAdjacentCell(1, 2, Direction::TOP)
        );
        $this->assertSame(
            $maze->getCell(2, 2),
            $maze->getAdjacentCell(1, 2, Direction::RIGHT)
        );
        $this->assertNull($maze->getAdjacentCell(1, 2, Direction::BOTTOM));
        $this->assertSame(
            $maze->getCell(0, 2),
            $maze->getAdjacentCell(1, 2, Direction::LEFT)
        );

        $this->assertSame(
            $maze->getCell(3, 1),
            $maze->getAdjacentCell(3, 2, Direction::TOP)
        );
        $this->assertNull($maze->getAdjacentCell(3, 2, Direction::RIGHT));
        $this->assertNull($maze->getAdjacentCell(3, 2, Direction::BOTTOM));
        $this->assertSame(
            $maze->getCell(2, 2),
            $maze->getAdjacentCell(3, 2, Direction::LEFT)
        );
    }

    public function testRemoveWalls()
    {
        $maze = new Maze(3, 2);

        $c0 = $maze->getCell(0, 0);
        $c1 = $maze->getCell(0, 1);

        $maze->removeWalls(0, 0, Direction::BOTTOM);

        $this->assertTrue($c0->topWall && $c0->rightWall && $c0->leftWall);
        $this->assertFalse($c0->bottomWall);
        $this->assertFalse($c1->topWall);
        $this->assertTrue($c1->bottomWall && $c1->rightWall && $c1->leftWall);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no adjacent cell');
        $maze->removeWalls(2, 0, Direction::TOP);
    }

    public function testRemoveWallsOuter()
    {
        $maze = new Maze(3, 2);

        $c = $maze->getCell(2, 0);

        $maze->removeWalls(2, 0, Direction::RIGHT, true);

        $this->assertTrue($c->topWall && $c->bottomWall && $c->leftWall);
        $this->assertFalse($c->rightWall);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Target cell is not edge cell');
        $maze->removeWalls(2, 0, Direction::LEFT, true);
    }

    public function testSetEntrance()
    {
        $maze = new Maze(3, 2);
        $this->assertNull($maze->getEntrance());

        $maze->setEntrance(Direction::TOP, 0);
        $this->assertEquals(
            new DoorPosition(Direction::TOP, 0),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(0, 0)->topWall);

        $maze->setEntrance(Direction::TOP, 2);
        $this->assertEquals(
            new DoorPosition(Direction::TOP, 2),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(2, 0)->topWall);

        $maze->setEntrance(Direction::RIGHT, 0);
        $this->assertEquals(
            new DoorPosition(Direction::RIGHT, 0),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(2, 0)->rightWall);

        $maze->setEntrance(Direction::RIGHT, 1);
        $this->assertEquals(
            new DoorPosition(Direction::RIGHT, 1),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(2, 1)->rightWall);

        $maze->setEntrance(Direction::BOTTOM, 2);
        $this->assertEquals(
            new DoorPosition(Direction::BOTTOM, 2),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(2, 1)->bottomWall);

        $maze->setEntrance(Direction::BOTTOM, 0);
        $this->assertEquals(
            new DoorPosition(Direction::BOTTOM, 0),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(0, 1)->bottomWall);

        $maze->setEntrance(Direction::LEFT, 1);
        $this->assertEquals(
            new DoorPosition(Direction::LEFT, 1),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(0, 1)->leftWall);

        $maze->setEntrance(Direction::LEFT, 0);
        $this->assertEquals(
            new DoorPosition(Direction::LEFT, 0),
            $maze->getEntrance()
        );
        $this->assertFalse($maze->getCell(0, 0)->leftWall);
    }

    public function testSetExit()
    {
        $maze = new Maze(3, 2);
        $this->assertNull($maze->getExit());

        $maze->setExit(Direction::TOP, 0);
        $this->assertEquals(
            new DoorPosition(Direction::TOP, 0),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(0, 0)->topWall);

        $maze->setExit(Direction::TOP, 2);
        $this->assertEquals(
            new DoorPosition(Direction::TOP, 2),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(2, 0)->topWall);

        $maze->setExit(Direction::RIGHT, 0);
        $this->assertEquals(
            new DoorPosition(Direction::RIGHT, 0),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(2, 0)->rightWall);

        $maze->setExit(Direction::RIGHT, 1);
        $this->assertEquals(
            new DoorPosition(Direction::RIGHT, 1),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(2, 1)->rightWall);

        $maze->setExit(Direction::BOTTOM, 2);
        $this->assertEquals(
            new DoorPosition(Direction::BOTTOM, 2),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(2, 1)->bottomWall);

        $maze->setExit(Direction::BOTTOM, 0);
        $this->assertEquals(
            new DoorPosition(Direction::BOTTOM, 0),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(0, 1)->bottomWall);

        $maze->setExit(Direction::LEFT, 1);
        $this->assertEquals(
            new DoorPosition(Direction::LEFT, 1),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(0, 1)->leftWall);

        $maze->setExit(Direction::LEFT, 0);
        $this->assertEquals(
            new DoorPosition(Direction::LEFT, 0),
            $maze->getExit()
        );
        $this->assertFalse($maze->getCell(0, 0)->leftWall);
    }

    public function testSetEntranceConflict()
    {
        $maze = new Maze(3, 2);
        $maze->setExit(Direction::TOP, 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This place is already assigned to Exit');
        $maze->setEntrance(Direction::TOP, 2);
    }

    public function testSetExitConflict()
    {
        $maze = new Maze(3, 2);
        $maze->setEntrance(Direction::TOP, 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This place is already assigned to Entrance');
        $maze->setExit(Direction::TOP, 2);
    }

    public function testGetEntranceCell()
    {
        $maze = new Maze(3, 2);
        $this->assertNull($maze->getEntranceCell());

        $maze->setEntrance(Direction::BOTTOM, 2);
        $this->assertSame($maze->getCell(2, 1), $maze->getEntranceCell());
    }

    public function testGetExitCell()
    {
        $maze = new Maze(3, 2);
        $this->assertNull($maze->getExitCell());

        $maze->setExit(Direction::BOTTOM, 2);
        $this->assertSame($maze->getCell(2, 1), $maze->getExitCell());
    }
}
