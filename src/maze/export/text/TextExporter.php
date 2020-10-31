<?php

namespace VovanVE\MazeProject\maze\export\text;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\MazeExporterInterface;

class TextExporter extends TextBaseConfig implements MazeExporterInterface
{
    public function configureExport(array $options): void
    {
        $this->configureBase($options);
    }

    public function exportMaze(Maze $maze): string
    {
        $w = $maze->getWidth();
        $h = $maze->getHeight();

        $charsCount = \mb_strlen($this->wall, 'UTF-8');
        $space = \str_repeat(' ', $charsCount);

        /** @var string[][] $lines */
        $lines = \array_fill(
            0,
            $h * 2 + 1,
            \array_fill(0, $w * 2 + 1, $this->wall)
        );

        for ($y = 0; $y < $h; $y++) {
            $sy = $y * 2 + 1;
            for ($x = 0; $x < $w; $x++) {
                $sx = $x * 2 + 1;
                $lines[$sy][$sx] = $space;

                $cell = $maze->getCell($x, $y);
                if (0 === $y && !$cell->topWall) {
                    $lines[$sy - 1][$sx] = $space;
                }
                if (0 === $x && !$cell->leftWall) {
                    $lines[$sy][$sx - 1] = $space;
                }
                if (!$cell->bottomWall) {
                    $lines[$sy + 1][$sx] = $space;
                }
                if (!$cell->rightWall) {
                    $lines[$sy][$sx + 1] = $space;
                }
            }
        }

        $in = $maze->getEntrance();
        $out = $maze->getExit();
        if ($in) {
            $inStr = $this->repeatStringToLength($this->in, $charsCount);
            $this->markDoor($lines, $in, $inStr);
        }
        if ($out) {
            $outStr = $this->repeatStringToLength($this->out, $charsCount);
            $this->markDoor($lines, $out, $outStr);
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
