<?php

namespace VovanVE\MazeProject\tests\unit\maze\export\json;

use VovanVE\MazeProject\maze\Config;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\json\JsonExporter;
use VovanVE\MazeProject\maze\export\json\JsonImporter;
use VovanVE\MazeProject\maze\Generator;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class JsonImporterTest extends BaseTestCase
{
    /**
     * @param string $input
     * @dataProvider dataImport
     */
    public function testImport(string $input)
    {
        $importer = new JsonImporter();
        $maze = $importer->importMaze($input);

        $this->assertInstanceOf(Maze::class, $maze);

        $this->assertJsonStringEqualsJsonString(
            $input,
            (new JsonExporter())->exportMaze($maze)
        );
    }

    public function dataImport(): array
    {
        return [
            [\json_encode([
                'width' => 1,
                'height' => 1,
                'in' => null,
                'out' => null,
                'cells' => ['0'],
            ])],
            [\json_encode([
                'width' => 2,
                'height' => 1,
                'in' => ['top', 1],
                'out' => ['right', 0],
                'cells' => ['00'],
            ])],
            [\json_encode([
                'width' => 1,
                'height' => 2,
                'in' => ['bottom', 0],
                'out' => ['left', 1],
                'cells' => ['2', '0'],
            ])],
            [\json_encode([
                'width' => 2,
                'height' => 2,
                'in' => ['left', 0],
                'out' => ['left', 1],
                'cells' => ['20', '00'],
            ])],
            [\json_encode([
                'width' => 3,
                'height' => 3,
                'in' => ['left', 0],
                'out' => ['right', 1],
                'cells' => ['200', '030', '010'],
            ])],
            [\json_encode([
                'width' => 3,
                'height' => 3,
                'in' => ['top', 0],
                'out' => ['bottom', 1],
                'cells' => ['100', '230', '010'],
            ])],
        ];
    }

    /**
     * @param string $input
     * @param string $errorMessage
     * @dataProvider dataImportFail
     */
    public function testImportFail(string $input, string $errorMessage)
    {
        $importer = new JsonImporter();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $importer->importMaze($input);
    }

    public function dataImportFail(): array
    {
        return [
            ['Fail', 'Invalid JSON'],
            ['{', 'Invalid JSON'],
            ['nullbug', 'Invalid JSON'],
            ['null bug', 'Invalid JSON'],

            ['42', 'Invalid JSON data - not an object'],
            ['"string"', 'Invalid JSON data - not an object'],
            ['true', 'Invalid JSON data - not an object'],
            ['false', 'Invalid JSON data - not an object'],
            ['null', 'Invalid JSON data - not an object'],
            [' null ', 'Invalid JSON data - not an object'],

            // width
            ['{}', 'The field `width` did not set'],

            ['{"width":null}', 'Value of `width` must be integer'],
            ['{"width":true}', 'Value of `width` must be integer'],
            ['{"width":false}', 'Value of `width` must be integer'],
            ['{"width":[]}', 'Value of `width` must be integer'],
            ['{"width":[42]}', 'Value of `width` must be integer'],
            ['{"width":{"x":1}}', 'Value of `width` must be integer'],
            ['{"width":"42"}', 'Value of `width` must be integer'],
            ['{"width":42.5}', 'Value of `width` must be integer'],

            ['{"width":0}', 'Value of `width` cannot be less than `1`'],
            ['{"width":-42}', 'Value of `width` cannot be less than `1`'],

            // height
            ['{"width":1}', 'The field `height` did not set'],

            ['{"width":1,"height":null}', 'Value of `height` must be integer'],
            ['{"width":1,"height":true}', 'Value of `height` must be integer'],
            ['{"width":1,"height":false}', 'Value of `height` must be integer'],
            ['{"width":1,"height":[]}', 'Value of `height` must be integer'],
            ['{"width":1,"height":[42]}', 'Value of `height` must be integer'],
            ['{"width":1,"height":{"x":1}}', 'Value of `height` must be integer'],
            ['{"width":1,"height":"42"}', 'Value of `height` must be integer'],
            ['{"width":1,"height":42.5}', 'Value of `height` must be integer'],

            ['{"width":1,"height":0}', 'Value of `height` cannot be less than `1`'],
            ['{"width":1,"height":-42}', 'Value of `height` cannot be less than `1`'],

            // in, out - absent
            ['{"width":1,"height":1}', 'The field `cells` did not set'],

            // in
            [
                '{"width":1,"height":1,"in":42}',
                'Value of `in` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"in":"string"}',
                'Value of `in` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"in":true}',
                'Value of `in` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"in":false}',
                'Value of `in` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"in":{"x":1}}',
                'Value of `in` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"in":[1,2,3]}',
                'Value of `in` must be an array of exactly two elements',
            ],

            [
                '{"width":1,"height":1,"in":[null,2]}',
                'Value of `in[0]` must be string',
            ],
            [
                '{"width":1,"height":1,"in":[42,2]}',
                'Value of `in[0]` must be string',
            ],
            [
                '{"width":1,"height":1,"in":["lol",2]}',
                'Value of `in[0]` must be "top", "right", "bottom" or "left"',
            ],
            [
                '{"width":1,"height":1,"in":["top",null]}',
                'Value of `in[1]` must be integer',
            ],
            [
                '{"width":1,"height":1,"in":["top",-1]}',
                'Value of `in[1]` cannot be less than `0`',
            ],
            [
                '{"width":1,"height":1,"in":["right",-1]}',
                'Value of `in[1]` cannot be less than `0`',
            ],
            [
                '{"width":1,"height":1,"in":["bottom",1]}',
                'Value of `in[1]` cannot be greater than `0`',
            ],
            [
                '{"width":1,"height":1,"in":["left",1]}',
                'Value of `in[1]` cannot be greater than `0`',
            ],

            // out
            [
                '{"width":1,"height":1,"out":42}',
                'Value of `out` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"out":"string"}',
                'Value of `out` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"out":true}',
                'Value of `out` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"out":false}',
                'Value of `out` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"out":{"x":1}}',
                'Value of `out` must be an array of exactly two elements',
            ],
            [
                '{"width":1,"height":1,"out":[1,2,3]}',
                'Value of `out` must be an array of exactly two elements',
            ],

            [
                '{"width":1,"height":1,"out":[null,2]}',
                'Value of `out[0]` must be string',
            ],
            [
                '{"width":1,"height":1,"out":[42,2]}',
                'Value of `out[0]` must be string',
            ],
            [
                '{"width":1,"height":1,"out":["lol",2]}',
                'Value of `out[0]` must be "top", "right", "bottom" or "left"',
            ],
            [
                '{"width":1,"height":1,"out":["top",null]}',
                'Value of `out[1]` must be integer',
            ],
            [
                '{"width":1,"height":1,"out":["top",-1]}',
                'Value of `out[1]` cannot be less than `0`',
            ],
            [
                '{"width":1,"height":1,"out":["right",-1]}',
                'Value of `out[1]` cannot be less than `0`',
            ],
            [
                '{"width":1,"height":1,"out":["bottom",1]}',
                'Value of `out[1]` cannot be greater than `0`',
            ],
            [
                '{"width":1,"height":1,"out":["left",1]}',
                'Value of `out[1]` cannot be greater than `0`',
            ],

            // cells
            [
                '{"width":1,"height":2,"cells":42}',
                'Value of `cells` must be array with `2` items',
            ],
            [
                '{"width":1,"height":42,"cells":42}',
                'Value of `cells` must be array with `42` items',
            ],
            [
                '{"width":1,"height":2,"cells":[]}',
                'Value of `cells` must be array with `2` items',
            ],
            [
                '{"width":1,"height":2,"cells":[1,2,3]}',
                'Value of `cells` must be array with `2` items',
            ],
            [
                '{"width":1,"height":2,"cells":{"1":42,"0":37}}',
                'Value of `cells` must be array with `2` items',
            ],

            [
                '{"width":3,"height":2,"cells":[42,37]}',
                'Value of `cells[0]` must be string of `3` digits',
            ],
            [
                '{"width":3,"height":2,"cells":["",37]}',
                'Value of `cells[0]` must be string of `3` digits',
            ],
            [
                '{"width":3,"height":2,"cells":["1",37]}',
                'Value of `cells[0]` must be string of `3` digits',
            ],
            [
                '{"width":3,"height":2,"cells":["12",37]}',
                'Value of `cells[0]` must be string of `3` digits',
            ],
            [
                '{"width":3,"height":2,"cells":["1234",37]}',
                'Value of `cells[0]` must be string of `3` digits',
            ],

            [
                '{"width":3,"height":2,"cells":["aaa",37]}',
                'String in `cells[0]` can to contain only `0`..`3` digits,' .
                ' and the last digit can be only `0` or `2`',
            ],
            [
                '{"width":3,"height":2,"cells":["33a",37]}',
                'String in `cells[0]` can to contain only `0`..`3` digits,' .
                ' and the last digit can be only `0` or `2`',
            ],
            [
                '{"width":3,"height":2,"cells":["333",37]}',
                'String in `cells[0]` can to contain only `0`..`3` digits,' .
                ' and the last digit can be only `0` or `2`',
            ],
            [
                '{"width":3,"height":2,"cells":["aa3",37]}',
                'String in `cells[0]` can to contain only `0`..`3` digits,' .
                ' and the last digit can be only `0` or `2`',
            ],
            [
                '{"width":3,"height":2,"cells":["aa0",37]}',
                'String in `cells[0]` can to contain only `0`..`3` digits,' .
                ' and the last digit can be only `0` or `2`',
            ],

            [
                '{"width":3,"height":2,"cells":["332","aaa"]}',
                'Last string in `cells[1]` can to contain only `0` and `1`' .
                ' digits, and the last digit can be only `0`',
            ],
            [
                '{"width":3,"height":2,"cells":["332","23a"]}',
                'Last string in `cells[1]` can to contain only `0` and `1`' .
                ' digits, and the last digit can be only `0`',
            ],
            [
                '{"width":3,"height":2,"cells":["332","111"]}',
                'Last string in `cells[1]` can to contain only `0` and `1`' .
                ' digits, and the last digit can be only `0`',
            ],
            [
                '{"width":3,"height":2,"cells":["332","aa1"]}',
                'Last string in `cells[1]` can to contain only `0` and `1`' .
                ' digits, and the last digit can be only `0`',
            ],
            [
                '{"width":3,"height":2,"cells":["332","aa0"]}',
                'Last string in `cells[1]` can to contain only `0` and `1`' .
                ' digits, and the last digit can be only `0`',
            ],
        ];
    }

    public function testRandomReimport()
    {
        $maze = (new Generator(new Config(30, 20, 5, '')))->generate();

        $text = (new JsonExporter())->exportMaze($maze);

        $importedMaze = (new JsonImporter())->importMaze($text);

        $this->assertEquals($maze, $importedMaze, 'Imported maze must be equal');
    }
}
