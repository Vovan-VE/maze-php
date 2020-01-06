<?php

namespace VovanVE\MazeProject\maze\export;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\maze\data\Maze;

class TextExporter implements MazeExporterInterface
{
    public $wallChar = '#';

    public function exportMaze(Maze $maze): string
    {
        $w = $maze->getWidth();
        $h = $maze->getHeight();

        /** @var string[][] $lines */
        $lines = \array_fill(0, $h * 2 + 1, \array_fill(0, $w * 2 + 1, $this->wallChar));

        for ($y = 0; $y < $h; $y++) {
            $sy = $y * 2 + 1;
            for ($x = 0; $x < $w; $x++) {
                $sx = $x * 2 + 1;
                $lines[$sy][$sx] = ' ';

                $cell = $maze->getCell($x, $y);
                if (0 === $y && !$cell->topWall) {
                    $lines[$sy - 1][$sx] = ' ';
                }
                if (0 === $x && !$cell->leftWall) {
                    $lines[$sy][$sx - 1] = ' ';
                }
                if (!$cell->bottomWall) {
                    $lines[$sy + 1][$sx] = ' ';
                }
                if (!$cell->rightWall) {
                    $lines[$sy][$sx + 1] = ' ';
                }
            }
        }

        $in = $maze->getEntrance();
        $out = $maze->getExit();
        if ($in) {
            $this->markDoor($lines, $in, 'i');
        }
        if ($out) {
            $this->markDoor($lines, $out, 'E');
        }

        return \join(\PHP_EOL, \array_map('join', $lines));
    }

    /**
     * @param string[][] $lines
     * @param DoorPosition $door
     * @param string $char
     */
    private function markDoor(
        array &$lines,
        DoorPosition $door,
        string $char
    ): void {
        switch ($door->getOuterWallSide()) {
            case Direction::TOP:
                $x = $door->getCellIndex() * 2 + 1;
                $y = 0;
                break;

            case Direction::RIGHT:
                $x = \count($lines[0]) - 1;
                $y = $door->getCellIndex() * 2 + 1;
                break;

            case Direction::BOTTOM:
                $x = $door->getCellIndex() * 2 + 1;
                $y = \count($lines) - 1;
                break;

            case Direction::LEFT:
                $x = 0;
                $y = $door->getCellIndex() * 2 + 1;
                break;

            default:
                throw new \LogicException('Invalid direction');
        }

        $lines[$y][$x] = $char;
    }
}
