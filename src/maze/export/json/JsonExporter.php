<?php

namespace VovanVE\MazeProject\maze\export\json;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\DoorPosition;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\MazeExporterInterface;

class JsonExporter implements MazeExporterInterface
{
    public $pretty = false;

    public function configureExport(array $options): void
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'pretty':
                    $this->pretty = true;
                    break;

                default:
                    throw new \InvalidArgumentException(
                        "Unknown option `$name`"
                    );
            }
        }
    }

    public function exportMaze(Maze $maze): string
    {
        $cells = [];

        $width = $maze->getWidth();
        $height = $maze->getHeight();

        $max_x = $width - 1;
        $max_y = $height - 1;
        for ($y = 0; $y <= $max_y; $y++) {
            $line = '';
            $show_bottom = $y < $max_y;
            for ($x = 0; $x <= $max_x; $x++) {
                $show_right = $x < $max_x;
                $cell = $maze->getCell($x, $y);

                if ($cell->rightWall && $show_right) {
                    if ($cell->bottomWall && $show_bottom) {
                        $line .= '3';
                    } else {
                        $line .= '1';
                    }
                } else {
                    if ($cell->bottomWall && $show_bottom) {
                        $line .= '2';
                    } else {
                        $line .= '0';
                    }
                }
            }
            $cells[] = $line;
        }

        $export = [
            'width' => $width,
            'height' => $height,
            'in' => $this->exportDoor($maze->getEntrance()),
            'out' => $this->exportDoor($maze->getExit()),
            'cells' => $cells,
        ];

        $options = 0;
        if ($this->pretty) {
            $options |= \JSON_PRETTY_PRINT;
        }

        return \json_encode($export, $options);
    }

    private function exportDoor(?DoorPosition $door): ?array
    {
        if (!$door) {
            return null;
        }
        switch ($door->getOuterWallSide()) {
            case Direction::TOP:
                $side = 'top';
                break;

            case Direction::RIGHT:
                $side = 'right';
                break;

            case Direction::BOTTOM:
                $side = 'bottom';
                break;

            case Direction::LEFT:
                $side = 'left';
                break;

            default:
                throw new \RuntimeException('Invalid direction');
        }

        return [$side, $door->getCellIndex()];
    }
}
