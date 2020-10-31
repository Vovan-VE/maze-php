<?php

namespace VovanVE\MazeProject\maze\format;

use VovanVE\MazeProject\maze\data\Maze;

interface MazeExporterInterface
{
    public function configureExport(array $options): void;

    public function exportMaze(Maze $maze): string;
}
