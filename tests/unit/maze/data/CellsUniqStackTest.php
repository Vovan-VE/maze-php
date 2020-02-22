<?php

namespace VovanVE\MazeProject\tests\unit\maze\data;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\CellsUniqStack;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class CellsUniqStackTest extends BaseTestCase
{
    public function testIterate()
    {
        $cellA = new Cell(1, 2);
        $cellB = new Cell(1, 3);
        $cellC = new Cell(2, 3);

        $stack = new CellsUniqStack();
        $stack->push($cellA);
        $stack->push($cellB);
        $stack->push($cellC);

        $this->assertSame(
            [$cellA, $cellB, $cellC],
            \iterator_to_array($stack->iterate())
        );
    }

    public function testManipulations()
    {
        $cellA = new Cell(1, 2);
        $cellB = new Cell(1, 3);
        $cellC = new Cell(2, 3);

        $stack = new CellsUniqStack();

        $this->assertTrue($stack->isEmpty());
        $this->assertFalse($stack->has($cellA));
        $this->assertFalse($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));

        $stack->push($cellA);
        $this->assertFalse($stack->isEmpty());
        $this->assertTrue($stack->has($cellA));
        $this->assertFalse($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));

        $stack->push($cellB);
        $this->assertFalse($stack->isEmpty());
        $this->assertTrue($stack->has($cellA));
        $this->assertTrue($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));

        $stack->push($cellC);
        $this->assertFalse($stack->isEmpty());
        $this->assertTrue($stack->has($cellA));
        $this->assertTrue($stack->has($cellB));
        $this->assertTrue($stack->has($cellC));

        $this->assertSame($cellC, $stack->pop());
        $this->assertFalse($stack->isEmpty());
        $this->assertTrue($stack->has($cellA));
        $this->assertTrue($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));

        $this->assertSame($cellB, $stack->pop());
        $this->assertFalse($stack->isEmpty());
        $this->assertTrue($stack->has($cellA));
        $this->assertFalse($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));

        $this->assertSame($cellA, $stack->pop());
        $this->assertTrue($stack->isEmpty());
        $this->assertFalse($stack->has($cellA));
        $this->assertFalse($stack->has($cellB));
        $this->assertFalse($stack->has($cellC));
    }

    public function testPopUntil()
    {
        $cellA = new Cell(1, 2);
        $cellB = new Cell(1, 3);
        $cellC = new Cell(2, 3);
        $cellD = new Cell(3, 3);
        $cellE = new Cell(3, 4);

        $stack = new CellsUniqStack();
        $stack->push($cellA);
        $stack->push($cellB);
        $stack->push($cellC);
        $stack->push($cellD);
        $stack->push($cellE);

        $stack->popUntil($cellB);

        $this->assertSame(
            [$cellA, $cellB],
            \iterator_to_array($stack->iterate())
        );
    }

    public function testPushDuplicate()
    {
        $stack = new CellsUniqStack();
        $stack->push(new Cell(1, 2));
        $stack->push(new Cell(1, 3));
        $stack->push(new Cell(2, 3));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The same cell is already in stack');
        $stack->push(new Cell(1, 3));
    }

    public function testPopEmpty()
    {
        $stack = new CellsUniqStack();
        $stack->push(new Cell(1, 2));
        $stack->pop();

        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('The stack is empty');
        $stack->pop();
    }

    public function testPopUntilNotFound()
    {
        $stack = new CellsUniqStack();
        $stack->push(new Cell(1, 2));
        $stack->push(new Cell(1, 3));
        $stack->push(new Cell(2, 3));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cell is not in stack');
        $stack->popUntil(new Cell(2, 2));
    }

    public function testIsPrevious()
    {
        $a = new Cell(1, 2);
        $b = new Cell(1, 3);
        $c = new Cell(2, 3);

        $stack = new CellsUniqStack();

        $this->assertFalse($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->push($a);
        $this->assertFalse($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->push($b);
        $this->assertTrue($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->push($c);
        $this->assertFalse($stack->isPrevious($a));
        $this->assertTrue($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->pop();
        $this->assertTrue($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->pop();
        $this->assertFalse($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));

        $stack->pop();
        $this->assertFalse($stack->isPrevious($a));
        $this->assertFalse($stack->isPrevious($b));
        $this->assertFalse($stack->isPrevious($c));
    }
}
