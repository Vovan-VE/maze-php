<?php

namespace VovanVE\MazeProject\maze\format;

use VovanVE\MazeProject\maze\data\Maze;

interface MazeImporterInterface
{
    public function configureImport(array $options): void;

    public function importMaze(string $input): Maze;
}
