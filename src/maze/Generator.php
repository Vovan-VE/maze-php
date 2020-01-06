<?php

namespace VovanVE\MazeProject\maze;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\CellsSet;
use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;

class Generator
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function generate(): Maze
    {
        $aFree = new CellsSet();
        $maze = new Maze($this->config->getWidth(), $this->config->getHeight());
        foreach ($maze->getAllCells() as $cell) {
            $aFree->add($cell);
        }

        $aFrontier = new CellsSet();

        $cStart = $aFree->getRandom();
        $aFree->remove($cStart);
        $aFrontier->add($cStart);

        while (!$aFree->isEmpty()) {
            $length = $this->config->getBranchLength();

            $cCurrent = $aFrontier->getRandom();
            while ($length-- > 0) {
                $dNext = Direction::random();
                $cNext = $this->findAdjacentFree(
                    $maze,
                    $aFree,
                    $cCurrent,
                    $dNext
                );
                $maze->removeWalls(
                    $cCurrent->getX(),
                    $cCurrent->getY(),
                    $dNext
                );
                $aFree->remove($cNext);
                if ($aFree->isEmpty()) {
                    break;
                }
                $this->updateFrontiers($maze, $aFrontier, $aFree, $cNext);
                if (!$this->cellHasFreeAdjacent($maze, $aFree, $cNext)) {
                    break;
                }
                $aFrontier->add($cNext);
                $cCurrent = $cNext;
            }
        }

        $maze->setEntrance(Direction::LEFT, \mt_rand(0, $maze->getHeight() - 1));
        $maze->setExit(Direction::RIGHT, \mt_rand(0, $maze->getHeight() - 1));

        return $maze;
    }

    private function findAdjacentFree(
        Maze $maze,
        CellsSet $aFree,
        Cell $cCurrent,
        int &$dStart
    ): Cell {
        $x = $cCurrent->getX();
        $y = $cCurrent->getY();
        for ($n = 4; $n-- > 0;) {
            $cNext = $maze->getAdjacentCell($x, $y, $dStart);
            if ($cNext && $aFree->has($cNext)) {
                return $cNext;
            }
            $dStart = Direction::next($dStart);
        }

        throw new \LogicException('There are no free cells ever?');
    }

    private function updateFrontiers(
        Maze $maze,
        CellsSet $aFrontier,
        CellsSet $aFree,
        Cell $cell
    ): void {
        $x = $cell->getX();
        $y = $cell->getY();
        for ($n = 4, $d = Direction::TOP; $n-- > 0; $d = Direction::next($d)) {
            $next = $maze->getAdjacentCell($x, $y, $d);
            if (
                $next &&
                $aFrontier->has($next) &&
                !$this->cellHasFreeAdjacent($maze, $aFree, $next)
            ) {
                $aFrontier->remove($next);
            }
        }
    }

    private function cellHasFreeAdjacent(
        Maze $maze,
        CellsSet $aFree,
        Cell $cell
    ): bool {
        $x = $cell->getX();
        $y = $cell->getY();
        for ($n = 4, $d = Direction::TOP; $n-- > 0; $d = Direction::next($d)) {
            $next = $maze->getAdjacentCell($x, $y, $d);
            if ($next && $aFree->has($next)) {
                return true;
            }
        }
        return false;
    }
}
