<?php

namespace VovanVE\MazeProject\tests\unit\maze;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\text\TextExporter;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;
use VovanVE\MazeProject\tests\helpers\TracingWalker;

class WalkerTest extends BaseTestCase
{
    public function testCreateMaze(): Maze
    {
        // ╶─┬─────┬─┐
        // ╷ └─┐ ╷ ╵ │
        // ├─╴ ╵ ├─╴ ╵
        // └─────┴───╴

        // TODO: use importer
        $maze = new Maze(5, 3);

        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::RIGHT, 2);

        $maze->removeWalls(0, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 0, Direction::RIGHT);
        $maze->removeWalls(2, 0, Direction::RIGHT);
        $maze->removeWalls(2, 0, Direction::BOTTOM);
        $maze->removeWalls(3, 0, Direction::BOTTOM);
        $maze->removeWalls(4, 0, Direction::BOTTOM);

        $maze->removeWalls(0, 1, Direction::RIGHT);
        $maze->removeWalls(1, 1, Direction::BOTTOM);
        $maze->removeWalls(2, 1, Direction::BOTTOM);
        $maze->removeWalls(3, 1, Direction::RIGHT);
        $maze->removeWalls(4, 1, Direction::BOTTOM);

        $maze->removeWalls(0, 2, Direction::RIGHT);
        $maze->removeWalls(1, 2, Direction::RIGHT);
        //$maze->removeWalls(2, 2, <nothing>);
        $maze->removeWalls(3, 2, Direction::RIGHT);
        //$maze->removeWalls(4, 2, <nothing>);

        // check if its done as expected
        $this->assertEquals(
            <<<'_END'
###########
i #     # #
# ### # # #
#   # #   #
### # ### #
#     #   E
###########
_END
            ,
            (new TextExporter())->exportMaze($maze)
        );

        return $maze;
    }

    private function getSolveCells(Maze $maze): array
    {
        return [
            $maze->getCell(0, 0),
            $maze->getCell(0, 1),
            $maze->getCell(1, 1),
            $maze->getCell(1, 2),
            $maze->getCell(2, 2),
            $maze->getCell(2, 1),
            $maze->getCell(2, 0),
            $maze->getCell(3, 0),
            $maze->getCell(3, 1),
            $maze->getCell(4, 1),
            $maze->getCell(4, 2),
        ];
    }

    /**
     * @param Maze $maze
     * @depends testCreateMaze
     */
    public function testWalkUseExit(Maze $maze)
    {
        $walker = new TracingWalker();
        $walker->useExit = true;
        $solve = $this->walkMaze($walker, $maze, 15);

        $this->assertSame(
            array_merge($this->getCommonTraceBeforeExit(), [
                'Turn [4,2] > | 11',
            ]),
            $walker->getLastTrace()
        );

        $this->assertSame(
            $this->getSolveCells($maze),
            \iterator_to_array($solve->iterate())
        );
    }

    /**
     * @param Maze $maze
     * @depends testCreateMaze
     */
    public function testWalkDontUseExit(Maze $maze)
    {
        $walker = new TracingWalker();
        $walker->useExit = false;
        $solve = $this->walkMaze($walker, $maze, 29);

        $this->assertSame(
            array_merge($this->getCommonTraceBeforeExit(), [
                'Turn [4,2] < | 11',
                'Step [3,2] < | 12',
                'Turn [3,2] > | 12',
                'Step [4,2] > | 11',
                'Turn [4,2] ^ | 11',
                'Step [4,1] ^ | 10',
                'Turn [4,1] < | 10',
                'Step [3,1] < | 9',
                'Turn [3,1] ^ | 9',
                'Step [3,0] ^ | 8',
                'Turn [3,0] < | 8',
                'Step [2,0] < | 7',
                'Turn [2,0] V | 7',
                'Step [2,1] V | 6',
                'Turn [2,1] V | 6',
                'Step [2,2] V | 5',
                'Turn [2,2] < | 5',
                'Step [1,2] < | 4',
                'Turn [1,2] < | 4',
                'Step [0,2] < | 5',
                'Turn [0,2] > | 5',
                'Step [1,2] > | 4',
                'Turn [1,2] ^ | 4',
                'Step [1,1] ^ | 3',
                'Turn [1,1] < | 3',
                'Step [0,1] < | 2',
                'Turn [0,1] ^ | 2',
                'Step [0,0] ^ | 1',
                'Turn [0,0] < | 1',
            ]),
            $walker->getLastTrace()
        );

        $this->assertNull($solve);
    }

    private function walkMaze(TracingWalker $walker, Maze $maze, ?int $stepsLimit)
    {
        try {
            return $walker->walk($maze, $stepsLimit);
        } catch (\RangeException $e) {
            if ($e->getMessage() === 'Reached steps limit') {
                throw new \RuntimeException(
                    'Failed trace' . \PHP_EOL . join(\PHP_EOL, $walker->getLastTrace()),
                    0,
                    $e
                );
            }
            throw $e;
        }
    }

    private function getCommonTraceBeforeExit()
    {
        return [
            'Step [0,0] > | 1',
            'Turn [0,0] V | 1',
            'Step [0,1] V | 2',
            'Turn [0,1] > | 2',
            'Step [1,1] > | 3',
            'Turn [1,1] V | 3',
            'Step [1,2] V | 4',
            'Turn [1,2] > | 4',
            'Step [2,2] > | 5',
            'Turn [2,2] ^ | 5',
            'Step [2,1] ^ | 6',
            'Turn [2,1] ^ | 6',
            'Step [2,0] ^ | 7',
            'Turn [2,0] < | 7',
            'Step [1,0] < | 8',
            'Turn [1,0] > | 8',
            'Step [2,0] > | 7',
            'Turn [2,0] > | 7',
            'Step [3,0] > | 8',
            'Turn [3,0] V | 8',
            'Step [3,1] V | 9',
            'Turn [3,1] > | 9',
            'Step [4,1] > | 10',
            'Turn [4,1] ^ | 10',
            'Step [4,0] ^ | 11',
            'Turn [4,0] V | 11',
            'Step [4,1] V | 10',
            'Turn [4,1] V | 10',
            'Step [4,2] V | 11',
        ];
    }

    public function testFailNoEntrance()
    {
        $walker = new TracingWalker();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given Maze has no Entrance');

        $walker->walk(new Maze(1, 1));
    }

    public function testFailUnexpectedHole()
    {
        $maze = new Maze(2, 2);
        // #####
        // i   #
        // ### #
        // E   #
        // # ###
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::LEFT, 1);
        $maze->removeWalls(0, 0, Direction::RIGHT);
        $maze->removeWalls(1, 0, Direction::BOTTOM);
        $maze->removeWalls(0, 1, Direction::RIGHT);
        // unexpected hole
        $maze->removeWalls(0, 1, Direction::BOTTOM, true);

        $walker = new TracingWalker();

        try {
            $walker->walk($maze, 5);
            $this->fail(
                'Unexpected walk success' . \PHP_EOL
                . join(PHP_EOL, $walker->getLastTrace())
            );
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals(\InvalidArgumentException::class, get_class($e));
            $this->assertEquals('Unexpected hole in the outer wall', $e->getMessage());
        }

        $this->assertSame(
            [
                'Step [0,0] > | 1',
                'Turn [0,0] > | 1',
                'Step [1,0] > | 2',
                'Turn [1,0] V | 2',
                'Step [1,1] V | 3',
                'Turn [1,1] < | 3',
                'Step [0,1] < | 4',
                'Turn [0,1] V | 4',
            ],
            $walker->getLastTrace()
        );
    }

    public function testShortcut()
    {
        // #############
        // i           #
        // ### # # # # #
        // E   #   #   #
        // #############
        $maze = new Maze(6, 2);
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::LEFT, 1);
        $maze->removeWalls(1, 0, Direction::LEFT);
        $maze->removeWalls(1, 0, Direction::RIGHT);
        $maze->removeWalls(1, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 1, Direction::LEFT);
        $maze->removeWalls(2, 1, Direction::TOP);
        $maze->removeWalls(2, 1, Direction::RIGHT);
        $maze->removeWalls(3, 0, Direction::LEFT);
        $maze->removeWalls(3, 0, Direction::RIGHT);
        $maze->removeWalls(3, 0, Direction::BOTTOM);
        $maze->removeWalls(4, 1, Direction::TOP);
        $maze->removeWalls(4, 1, Direction::RIGHT);
        $maze->removeWalls(5, 0, Direction::LEFT);
        $maze->removeWalls(5, 0, Direction::BOTTOM);

        // check if its done as expected
        $this->assertEquals(
            <<<'_END'
#############
i           #
### # # # # #
E   #   #   #
#############
_END
            ,
            (new TextExporter())->exportMaze($maze)
        );

        $walker = new TracingWalker();
        $walker->useExit = true;
        $solve = $this->walkMaze($walker, $maze, 16);

        $this->assertEquals(
            [
                'Step [0,0] > | 1',
                'Turn [0,0] > | 1',
                'Step [1,0] > | 2',
                'Turn [1,0] > | 2',
                'Step [2,0] > | 3',
                'Turn [2,0] > | 3',
                'Step [3,0] > | 4',
                'Turn [3,0] > | 4',
                'Step [4,0] > | 5',
                'Turn [4,0] > | 5',
                'Step [5,0] > | 6',
                'Turn [5,0] V | 6',
                'Step [5,1] V | 7',
                'Turn [5,1] < | 7',
                'Step [4,1] < | 8',
                'Turn [4,1] ^ | 8',
                'Shortcut [4,1] to [4,0] ^',
                'Step [4,0] ^ | 5',
                'Turn [4,0] < | 5',
                'Step [3,0] < | 4',
                'Turn [3,0] V | 4',
                'Step [3,1] V | 5',
                'Turn [3,1] < | 5',
                'Step [2,1] < | 6',
                'Turn [2,1] ^ | 6',
                'Shortcut [2,1] to [2,0] ^',
                'Step [2,0] ^ | 3',
                'Turn [2,0] < | 3',
                'Step [1,0] < | 2',
                'Turn [1,0] V | 2',
                'Step [1,1] V | 3',
                'Turn [1,1] < | 3',
                'Step [0,1] < | 4',
                'Turn [0,1] < | 4',
            ],
            $walker->getLastTrace()
        );

        $this->assertSame(
            [
                $maze->getCell(0, 0),
                $maze->getCell(1, 0),
                $maze->getCell(1, 1),
                $maze->getCell(0, 1),
            ],
            \iterator_to_array($solve->iterate())
        );
    }
}
