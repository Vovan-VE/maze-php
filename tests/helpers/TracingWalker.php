<?php

namespace VovanVE\MazeProject\tests\helpers;

use VovanVE\MazeProject\maze\data\Cell;
use VovanVE\MazeProject\maze\data\CellsUniqStack;
use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\Walker;

class TracingWalker extends Walker
{
    public $useExit = false;

    private $lastTrace;

    private const DIRECTION_CHAR = [
        Direction::TOP => '^',
        Direction::RIGHT => '>',
        Direction::BOTTOM => 'V',
        Direction::LEFT => '<',
    ];

    /**
     * @return string[]|null
     */
    public function getLastTrace(): ?array
    {
        return $this->lastTrace;
    }

    public function walk(Maze $maze, ?int $maxSteps = null): ?CellsUniqStack
    {
        $this->lastTrace = [];

        $this->didTurn = function (
            Cell $cell,
            int $look,
            \Traversable $aSolve
        ) {
            $this->log('Turn', $cell, $look, $aSolve);
        };

        $stepsRemained = $maxSteps;
        $this->didStep = function (
            Cell $cell,
            int $look,
            \Traversable $aSolve
        ) use (&$stepsRemained) {
            if (null !== $stepsRemained && $stepsRemained-- === 0) {
                throw new \RangeException('Reached steps limit');
            }
            $this->log('Step', $cell, $look, $aSolve);
        };

        try {
            return $this->walkMaze($maze);
        } finally {
            $this->didTurn = null;
            $this->didStep = null;
        }
    }

    protected function willShortcut(Cell $from, Cell $to, int $at): void
    {
        $this->lastTrace[] = sprintf(
            'Shortcut [%d,%d] to [%d,%d] %s',
            $from->getX(),
            $from->getY(),
            $to->getX(),
            $to->getY(),
            self::DIRECTION_CHAR[$at]
        );
    }

    protected function log(
        string $action,
        Cell $cell,
        int $look,
        \Traversable $aSolve
    ) {
        /** @var Cell[] $solve */
        $solve = \iterator_to_array($aSolve);
        /** @var Cell|false $last */
        $last = end($solve);

        if ($solve && !$cell->isSameCoords($last)) {
            throw new \UnexpectedValueException(
                sprintf(
                    "The last cell [%d,%d] in solve array is not the current cell [%d,%d]",
                    $last->getX(),
                    $last->getY(),
                    $cell->getX(),
                    $cell->getY()
                )
            );
        }

        $this->lastTrace[] = sprintf(
            "%s [%d,%d] %s | %d",
            $action,
            $cell->getX(),
            $cell->getY(),
            self::DIRECTION_CHAR[$look],
            count($solve)
        );
    }
}
