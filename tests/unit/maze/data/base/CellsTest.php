<?php

namespace VovanVE\MazeProject\tests\unit\maze\data\base;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\tests\helpers\BaseCells;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class CellsTest extends BaseTestCase
{
    public function testBase()
    {
        $cells = new BaseCells();

        $cellA = new Cell(42, 37);
        $cellB = new Cell(97, 23);

        $cellBCopy = clone $cellB;

        $this->assertTrue($cells->isEmpty());
        $this->assertFalse($cells->has($cellA));
        $this->assertFalse($cells->has($cellB));
        $this->assertFalse($cells->has($cellBCopy));

        $cells->setCells($cellA);
        $this->assertFalse($cells->isEmpty());
        $this->assertTrue($cells->has($cellA));
        $this->assertFalse($cells->has($cellB));
        $this->assertFalse($cells->has($cellBCopy));

        $cells->setCells($cellA, $cellB);
        $this->assertFalse($cells->isEmpty());
        $this->assertTrue($cells->has($cellA));
        $this->assertTrue($cells->has($cellB));
        $this->assertTrue($cells->has($cellBCopy));

        $cells->setCells($cellB);
        $this->assertFalse($cells->isEmpty());
        $this->assertFalse($cells->has($cellA));
        $this->assertTrue($cells->has($cellB));
        $this->assertTrue($cells->has($cellBCopy));

        $cells->setCells();
        $this->assertTrue($cells->isEmpty());
        $this->assertFalse($cells->has($cellA));
        $this->assertFalse($cells->has($cellB));
        $this->assertFalse($cells->has($cellBCopy));
    }
}
