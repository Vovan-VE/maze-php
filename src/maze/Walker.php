<?php

namespace VovanVE\MazeProject\maze;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\CellsUniqStack;
use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;

class Walker
{
    protected $useExit = false;
    /**
     * Optional callback after a step was performed
     * @var \Closure `(Cell $cell, int $look, \Traversable $aSolve): void`
     */
    protected $didStep;
    /**
     * Optional callback after direction for next step was determined
     * @var \Closure `(Cell $cell, int $look, \Traversable $aSolve): void`
     */
    protected $didTurn;

    protected function walkMaze(Maze $maze): ?CellsUniqStack
    {
        $cStart = $maze->getEntranceCell();
        if (!$cStart) {
            throw new \InvalidArgumentException('The given Maze has no Entrance');
        }
        $dStart = $maze->getEntrance()->getOuterWallSide();

        $cExit = $maze->getExitCell();
        if ($cExit) {
            $dExit = $maze->getExit()->getOuterWallSide();
        } else {
            $dExit = null;
        }

        /** @var Cell $cCurrent */
        $cCurrent = $cStart;
        $dLook = Direction::opposite($dStart);
        $aSolve = new CellsUniqStack();
        $aSolve->push($cCurrent);

        while (true) {
            if ($this->didStep) {
                ($this->didStep)($cCurrent, $dLook, $aSolve->iterate());
            }

            // find next direction to go to
            // default is backward
            $dNext = Direction::opposite($dLook);
            foreach (
                [Direction::prev($dLook), $dLook, Direction::next($dLook)]
                as $dTurn
            ) {
                if (!$cCurrent->hasWallAt($dTurn)) {
                    if (
                        $this->useExit ||
                        !(
                            $cExit &&
                            $cCurrent->isSameCoords($cExit) &&
                            $dTurn === $dExit
                        )
                    ) {
                        $dNext = $dTurn;
                        break;
                    }
                }
            }
            if ($this->didTurn) {
                ($this->didTurn)($cCurrent, $dNext, $aSolve->iterate());
            }

            if (
                $this->useExit &&
                $dNext === $dExit &&
                $cCurrent->isSameCoords($cExit)
            ) {
                return $aSolve;
            }

            if ($dNext === $dStart && $cCurrent->isSameCoords($cStart)) {
                return null;
            }

            $cNext = $maze->getAdjacentCell(
                $cCurrent->getX(),
                $cCurrent->getY(),
                $dNext
            );
            if (!$cNext) {
                throw new \InvalidArgumentException(
                    'Unexpected hole in the outer wall'
                );
            }

            if ($aSolve->isPrevious($cNext)) {
                $aSolve->pop();
            } elseif ($aSolve->has($cNext)) {
                $this->willShortcut($cCurrent, $cNext, $dNext);
                $aSolve->popUntil($cNext);
            } else {
                $aSolve->push($cNext);
            }

            $cCurrent = $cNext;
            $dLook = $dNext;
        }
        // Stupid IDE! We never will get here!
        return null;
    }

    protected function willShortcut(Cell $from, Cell $to, int $at): void
    {
        // no-op
    }
}
