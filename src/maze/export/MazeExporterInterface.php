<?php

namespace VovanVE\MazeProject\maze\export;

use VovanVE\MazeProject\maze\data\Maze;

interface MazeExporterInterface
{
    public function exportMaze(Maze $maze): string;
}
