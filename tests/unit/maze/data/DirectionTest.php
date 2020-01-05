<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class DirectionTest extends BaseTestCase
{
    public function testRandom()
    {
        $all = [
            Direction::TOP => 'top',
            Direction::RIGHT => 'right',
            Direction::BOTTOM => 'bottom',
            Direction::LEFT => 'left',
        ];

        for ($limit = 100; $limit-- > 0 && $all;) {
            $direction = Direction::random();
            unset($all[$direction]);
        }
        $this->assertCount(
            0,
            $all,
            'Some directions never returned from `random()`' . join(
                ',',
                $all
            )
        );
    }

    public function testNext()
    {
        $this->assertEquals(
            Direction::RIGHT,
            Direction::next(Direction::TOP)
        );
        $this->assertEquals(
            Direction::BOTTOM,
            Direction::next(Direction::RIGHT)
        );
        $this->assertEquals(
            Direction::LEFT,
            Direction::next(Direction::BOTTOM)
        );
        $this->assertEquals(
            Direction::TOP,
            Direction::next(Direction::LEFT)
        );
    }

    public function testOpposite()
    {
        $this->assertEquals(
            Direction::BOTTOM,
            Direction::opposite(Direction::TOP)
        );
        $this->assertEquals(
            Direction::LEFT,
            Direction::opposite(Direction::RIGHT)
        );
        $this->assertEquals(
            Direction::TOP,
            Direction::opposite(Direction::BOTTOM)
        );
        $this->assertEquals(
            Direction::RIGHT,
            Direction::opposite(Direction::LEFT)
        );
    }

    /**
     * @param $x
     * @param $y
     * @param $direction
     * @param $rx
     * @param $ry
     * @dataProvider dataAdjacentCoords
     */
    public function testAdjacentCoords($x, $y, $direction, $rx, $ry)
    {
        $this->assertEquals(
            [$rx, $ry],
            Direction::adjacentCoords($x, $y, $direction)
        );
    }

    public function dataAdjacentCoords()
    {
        return [
            [0, 0, Direction::TOP, 0, -1],
            [0, 0, Direction::RIGHT, 1, 0],
            [0, 0, Direction::BOTTOM, 0, 1],
            [0, 0, Direction::LEFT, -1, 0],
            [10, 10, Direction::TOP, 10, 9],
            [10, 10, Direction::RIGHT, 11, 10],
            [10, 10, Direction::BOTTOM, 10, 11],
            [10, 10, Direction::LEFT, 9, 10],
        ];
    }
}
