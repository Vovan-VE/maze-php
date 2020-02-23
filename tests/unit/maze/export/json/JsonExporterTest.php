<?php

namespace VovanVE\MazeProject\tests\unit\maze\export\json;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\json\JsonExporter;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class JsonExporterTest extends BaseTestCase
{
    public function testExportAllWalls()
    {
        $maze = new Maze(3, 3);
        $exporter = new JsonExporter();

        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => null,
            'out' => null,
            'cells' => [
                '332',
                '332',
                '110',
            ],
        ]);
        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );
    }

    public function testExport()
    {
        $exporter = new JsonExporter();
        $maze = new Maze(3, 3);

        // remove some walls to be like a real maze
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => null,
            'out' => null,
            'cells' => [
                '210',
                '102',
                '000',
            ],
        ]);
        $maze->removeWalls(0, 0, Direction::RIGHT);
        $maze->removeWalls(1, 0, Direction::BOTTOM);
        $maze->removeWalls(2, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 1, Direction::RIGHT);
        $maze->removeWalls(1, 1, Direction::BOTTOM);
        $maze->removeWalls(0, 1, Direction::BOTTOM);
        $maze->removeWalls(0, 2, Direction::RIGHT);
        $maze->removeWalls(1, 2, Direction::RIGHT);

        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );

        // remove some outer walls to test all its possible cases
        // JSON exporter does not export outer walls, so the result is the same
        $maze->removeWalls(0, 0, Direction::TOP, true);
        $maze->removeWalls(0, 0, Direction::LEFT, true);
        $maze->removeWalls(2, 0, Direction::TOP, true);
        $maze->removeWalls(2, 0, Direction::RIGHT, true);
        $maze->removeWalls(0, 2, Direction::BOTTOM, true);
        $maze->removeWalls(0, 2, Direction::LEFT, true);
        $maze->removeWalls(2, 2, Direction::RIGHT, true);
        $maze->removeWalls(2, 2, Direction::BOTTOM, true);

        $this->assertJsonStringEqualsJsonString($result, $exporter->exportMaze($maze));

        // remove all the rest walls to check
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => null,
            'out' => null,
            'cells' => [
                '000',
                '000',
                '000',
            ],
        ]);
        $maze->removeWalls(0, 0, Direction::BOTTOM);
        $maze->removeWalls(1, 0, Direction::TOP, true);
        $maze->removeWalls(1, 0, Direction::RIGHT);
        $maze->removeWalls(0, 1, Direction::LEFT, true);
        $maze->removeWalls(0, 1, Direction::RIGHT);
        $maze->removeWalls(2, 1, Direction::RIGHT, true);
        $maze->removeWalls(2, 1, Direction::BOTTOM);
        $maze->removeWalls(1, 2, Direction::BOTTOM, true);

        $this->assertJsonStringEqualsJsonString($result, $exporter->exportMaze($maze));
    }

    public function testExportDoors()
    {
        $exporter = new JsonExporter();

        $maze = new Maze(3, 3);
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::RIGHT, 0);
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => ['left', 0],
            'out' => ['right', 0],
            'cells' => [
                '332',
                '332',
                '110',
            ],
        ]);
        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );

        $maze = new Maze(3, 3);
        $maze->setEntrance(Direction::LEFT, 1);
        $maze->setExit(Direction::RIGHT, 2);
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => ['left', 1],
            'out' => ['right', 2],
            'cells' => [
                '332',
                '332',
                '110',
            ],
        ]);
        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );

        $maze = new Maze(3, 3);
        $maze->setEntrance(Direction::TOP, 0);
        $maze->setExit(Direction::BOTTOM, 0);
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => ['top', 0],
            'out' => ['bottom', 0],
            'cells' => [
                '332',
                '332',
                '110',
            ],
        ]);
        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );

        $maze = new Maze(3, 3);
        $maze->setEntrance(Direction::TOP, 2);
        $maze->setExit(Direction::BOTTOM, 2);
        $result = \json_encode([
            'width' => 3,
            'height' => 3,
            'in' => ['top', 2],
            'out' => ['bottom', 2],
            'cells' => [
                '332',
                '332',
                '110',
            ],
        ]);
        $this->assertJsonStringEqualsJsonString(
            $result,
            $exporter->exportMaze($maze)
        );
    }

    public function testPretty()
    {
        $maze = new Maze(2, 2);
        $maze->setEntrance(Direction::LEFT, 0);
        $maze->setExit(Direction::RIGHT, 1);
        $exporter = new JsonExporter();

        $no_pretty = $exporter->exportMaze($maze);
        $expected = <<<'_JSON'
{"width":2,"height":2,"in":["left",0],"out":["right",1],"cells":["32","10"]}
_JSON;

        $this->assertEquals($expected, $no_pretty);

        $exporter->pretty = true;
        $pretty = $exporter->exportMaze($maze);
        $expected = <<<'_JSON'
{
    "width": 2,
    "height": 2,
    "in": [
        "left",
        0
    ],
    "out": [
        "right",
        1
    ],
    "cells": [
        "32",
        "10"
    ]
}
_JSON;

        $this->assertEquals($expected, $pretty);
    }

    public function testConfigure()
    {
        $exporter = new JsonExporter();
        $exporter->configureExport([
            'pretty' => true,
        ]);

        $this->assertTrue($exporter->pretty);
    }

    public function testConfigureFail()
    {
        $exporter = new JsonExporter();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown option `UNKNOWN_42`');

        $exporter->configureExport(['UNKNOWN_42' => '']);
    }
}
