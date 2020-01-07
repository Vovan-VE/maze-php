<?php

namespace VovanVE\MazeProject\maze\export;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\maze\data\Maze;

class TextExporter implements MazeExporterInterface
{
    public $wall = '#';
    public $in = 'i';
    public $out = 'E';

    public function configureExport(array $options): void
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'wall':
                    $this->validateOptionValueString($name, $value);
                    $this->wall = $value;
                    break;

                case 'in':
                    $this->validateOptionValueString($name, $value);
                    $this->in = $value;
                    break;

                case 'out':
                    $this->validateOptionValueString($name, $value);
                    $this->out = $value;
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown option `$name`");
            }
        }
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

    /**
     * @param string $str
     * @param int $length
     * @return string
     */
    private function repeatStringToLength(string $str, int $length): string
    {
        return \mb_substr(
            \str_repeat(
                $str,
                \ceil($length / \mb_strlen($str, 'UTF-8'))
            ),
            0,
            $length,
            'UTF-8'
        );
    }

    private function validateOptionValueString(string $name, $value): void
    {
        if (!\is_string($value) || '' === $value) {
            throw new \InvalidArgumentException(
                "Value for `$name` must be non empty string"
            );
        }
    }
}
