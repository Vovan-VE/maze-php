<?php

namespace VovanVE\MazeProject\maze\export;

use VovanVE\MazeProject\maze\data\Maze;

interface MazeImporterInterface
{
    public function importMaze(string $input): Maze;
}
