<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\CellsSet;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class CellsSetTest extends BaseTestCase
{
    public function testManipulations()
    {
        $set = new CellsSet();
        $cell_00 = new Cell(0, 0);
        $cell_01 = new Cell(0, 1);

        $this->assertTrue($set->isEmpty());
        $this->assertFalse($set->has($cell_00));
        $this->assertFalse($set->has($cell_01));

        $set->add($cell_00);
        $this->assertFalse($set->isEmpty());
        $this->assertTrue($set->has($cell_00));
        $this->assertFalse($set->has($cell_01));

        $set->add($cell_01);
        // add the same again - strange, but not an error
        $set->add($cell_01);
        $this->assertFalse($set->isEmpty());
        $this->assertTrue($set->has($cell_00));
        $this->assertTrue($set->has($cell_01));

        $rest = [$cell_00, $cell_01];
        for ($i = 10; $i-- > 0 && $rest;) {
            $cell = $set->getRandom();
            $rest = array_filter($rest, function (Cell $c) use ($cell) {
                return $c !== $cell;
            });
        }
        $this->assertCount(0, $rest, 'All cells was picked randomly');

        $set->remove($cell_00);
        // remove any unrelated cell - not an error
        $set->remove($cell_00);
        $this->assertFalse($set->isEmpty());
        $this->assertFalse($set->has($cell_00));
        $this->assertTrue($set->has($cell_01));

        $set->remove($cell_01);
        $this->assertTrue($set->isEmpty());
        $this->assertFalse($set->has($cell_00));
        $this->assertFalse($set->has($cell_01));
    }

    public function testAddConflict()
    {
        $set = new CellsSet();
        $cell_1 = new Cell(42, 37);
        $cell_2 = new Cell(42, 37);

        $set->add($cell_1);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Trying to add different cell with the same coords'
        );
        $set->add($cell_2);
    }
}
