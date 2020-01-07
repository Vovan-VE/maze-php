<?php

namespace VovanVE\MazeProject\tests\unit\maze\export;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\TextExporter;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class TextExporterTest extends BaseTestCase
{
    public function testExportAllWalls()
    {
        $maze = new Maze(3, 3);
        $exporter = new TextExporter();

        $result = \join(\PHP_EOL, [
            '#######',
            '# # # #',
            '#######',
            '# # # #',
            '#######',
            '# # # #',
            '#######',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));
    }

    public function testExportCustomWall()
    {
        $maze = new Maze(3, 3);
        $exporter = new TextExporter();
        $exporter->wall = '▒';

        $result = \join(\PHP_EOL, [
            '▒▒▒▒▒▒▒',
            '▒ ▒ ▒ ▒',
            '▒▒▒▒▒▒▒',
            '▒ ▒ ▒ ▒',
            '▒▒▒▒▒▒▒',
            '▒ ▒ ▒ ▒',
            '▒▒▒▒▒▒▒',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));
    }

    public function testExportCustomWallMultiple()
    {
        $maze = new Maze(3, 3);
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::RIGHT, 2);

        $exporter = new TextExporter();
        $exporter->wall = '▓█▓';
        $exporter->in = '()';

        $result = \join(\PHP_EOL, [
            '▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓',
            '()(   ▓█▓   ▓█▓   ▓█▓',
            '▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓',
            '▓█▓   ▓█▓   ▓█▓   ▓█▓',
            '▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓',
            '▓█▓   ▓█▓   ▓█▓   EEE',
            '▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓▓█▓',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));
    }

    public function testExport()
    {
        $exporter = new TextExporter();
        $maze = new Maze(3, 3);

        // remove some walls to be like a real maze
        $result = \join(\PHP_EOL, [
            '#######',
            '#   # #',
            '### # #',
            '# #   #',
            '# # ###',
            '#     #',
            '#######',
        ]);
        $maze->removeWalls(0, 0, Direction::RIGHT);
        $maze->removeWalls(1, 0, Direction::BOTTOM);
        $maze->removeWalls(2, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 1, Direction::RIGHT);
        $maze->removeWalls(1, 1, Direction::BOTTOM);
        $maze->removeWalls(0, 1, Direction::BOTTOM);
        $maze->removeWalls(0, 2, Direction::RIGHT);
        $maze->removeWalls(1, 2, Direction::RIGHT);

        $this->assertEquals($result, $exporter->exportMaze($maze));

        // remove some outer walls to test all its possible cases
        $result = \join(\PHP_EOL, [
            '# ### #',
            '    #  ',
            '### # #',
            '# #   #',
            '# # ###',
            '       ',
            '# ### #',
        ]);
        $maze->removeWalls(0, 0, Direction::TOP, true);
        $maze->removeWalls(0, 0, Direction::LEFT, true);
        $maze->removeWalls(2, 0, Direction::TOP, true);
        $maze->removeWalls(2, 0, Direction::RIGHT, true);
        $maze->removeWalls(0, 2, Direction::BOTTOM, true);
        $maze->removeWalls(0, 2, Direction::LEFT, true);
        $maze->removeWalls(2, 2, Direction::RIGHT, true);
        $maze->removeWalls(2, 2, Direction::BOTTOM, true);

        $this->assertEquals($result, $exporter->exportMaze($maze));

        // remove all the rest walls to check
        $result = \join(\PHP_EOL, [
            '# # # #',
            '       ',
            '# # # #',
            '       ',
            '# # # #',
            '       ',
            '# # # #',
        ]);
        $maze->removeWalls(0, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 0, Direction::TOP, true);
        $maze->removeWalls(1, 0, Direction::RIGHT);
        $maze->removeWalls(0, 1, Direction::LEFT, true);
        $maze->removeWalls(0, 1, Direction::RIGHT);
        $maze->removeWalls(2, 1, Direction::RIGHT, true);
        $maze->removeWalls(2, 1, Direction::BOTTOM);
        $maze->removeWalls(1, 2, Direction::BOTTOM, true);

        $this->assertEquals($result, $exporter->exportMaze($maze));
    }

    public function testExportDoors()
    {
        $exporter = new TextExporter();

        $maze = new Maze(3, 2);
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::RIGHT, 0);
        $result = \join(\PHP_EOL, [
            '#######',
            'i # # E',
            '#######',
            '# # # #',
            '#######',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));

        $maze = new Maze(3, 2);
        $maze->setEntrance(Direction::LEFT, 1);
        $maze->setExit(Direction::RIGHT, 1);
        $result = \join(\PHP_EOL, [
            '#######',
            '# # # #',
            '#######',
            'i # # E',
            '#######',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));

        $maze = new Maze(3, 2);
        $maze->setEntrance(Direction::TOP, 0);
        $maze->setExit(Direction::BOTTOM, 0);
        $result = \join(\PHP_EOL, [
            '#i#####',
            '# # # #',
            '#######',
            '# # # #',
            '#E#####',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));

        $maze = new Maze(3, 2);
        $maze->setEntrance(Direction::TOP, 2);
        $maze->setExit(Direction::BOTTOM, 2);
        $result = \join(\PHP_EOL, [
            '#####i#',
            '# # # #',
            '#######',
            '# # # #',
            '#####E#',
        ]);
        $this->assertEquals($result, $exporter->exportMaze($maze));
    }
}
