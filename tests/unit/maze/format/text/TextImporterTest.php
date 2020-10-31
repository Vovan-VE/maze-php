<?php

namespace VovanVE\MazeProject\tests\unit\maze\format\text;

use VovanVE\MazeProject\maze\Config;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\format\text\TextExporter;
use VovanVE\MazeProject\maze\format\text\TextImporter;
use VovanVE\MazeProject\maze\Generator;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class TextImporterTest extends BaseTestCase
{
    /**
     * @param string $input
     * @dataProvider dataImport
     */
    public function testImport(string $input)
    {
        $importer = new TextImporter();
        $maze = $importer->importMaze($input);

        $this->assertInstanceOf(Maze::class, $maze);

        $this->assertEquals(
            $input,
            (new TextExporter())->exportMaze($maze)
        );
    }

    public function dataImport(): array
    {
        return [
            [
                <<<TEXT
###
# #
###
TEXT
            ],
            [
                <<<TEXT
###i#
#   E
#####
TEXT
            ],
            [
                <<<TEXT
###
# #
###
E #
#i#
TEXT
            ],
            [
                <<<TEXT
#####
i   #
### #
E   #
#####
TEXT
            ],
            [
                <<<TEXT
#######
i     #
### # #
#   # E
# ### #
#   # #
#######
TEXT
            ],
            [
                <<<TEXT
#i#####
# #   #
# # # #
#   # #
##### #
#   # #
###E###
TEXT
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $errorMessage
     * @dataProvider dataImportFail
     */
    public function testImportFail(string $input, string $errorMessage)
    {
        $importer = new TextImporter();
        $importer->wall = '[#]';
        $importer->in = '(i)';
        $importer->out = '(E)';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $importer->importMaze($input);
    }

    public function dataImportFail(): array
    {
        return [
            // detect height
            [
                "",
                'Invalid lines count: must be odd number of lines, and at least 3 lines',
            ],
            [
                "#",
                'Invalid lines count: must be odd number of lines, and at least 3 lines',
            ],
            [
                "#\n" .
                "#",
                'Invalid lines count: must be odd number of lines, and at least 3 lines',
            ],
            [
                "#\n" .
                "#\n" .
                "#\n" .
                "#",
                'Invalid lines count: must be odd number of lines, and at least 3 lines',
            ],

            // detect width
            [
                "#\n" .
                "#\n" .
                "#",
                "Invalid line length: must be exact number of repeats of wall" .
                " chars count: 3 * n expected, but 1 given",
            ],
            [
                "##\n" .
                "#\n" .
                "#",
                "Invalid line length: must be exact number of repeats of wall" .
                " chars count: 3 * n expected, but 2 given",
            ],
            [
                "####\n" .
                "#\n" .
                "#",
                "Invalid line length: must be exact number of repeats of wall" .
                " chars count: 3 * n expected, but 4 given",
            ],
            [
                "[#]\n" .
                "#\n" .
                "#",
                "Invalid line length: must be odd number of repeats of wall" .
                " chars count: 3 * (2n+1), where n>=1",
            ],
            [
                "[#][#]\n" .
                "#\n" .
                "#",
                "Invalid line length: must be odd number of repeats of wall" .
                " chars count: 3 * (2n+1), where n>=1",
            ],
            [
                "[#][#][#][#]\n" .
                "#\n" .
                "#",
                "Invalid line length: must be odd number of repeats of wall" .
                " chars count: 3 * (2n+1), where n>=1",
            ],
            [
                "[#][#][#]\n" .
                "#\n" .
                "#",
                "Invalid line length: line 2 must be 9 chars",
            ],
            [
                "[#][#][#]\n" .
                "[#]   [#]\n" .
                "[#][#]..",
                "Invalid line length: line 3 must be 9 chars",
            ],

            // detect doors
            [
                "[#](i)[#]\n" .
                "[#]   [#]\n" .
                "[#](i)[#]",
                'Duplicate entrance: line 3 column 4',
            ],
            [
                "[#](E)[#]\n" .
                "[#]   [#]\n" .
                "[#](E)[#]",
                'Duplicate exit: line 3 column 4',
            ],
            [
                "[#]...[#]\n" .
                "[#]   [#]\n" .
                "[#][#][#]",
                'Not a wall at line 1 column 4',
            ],
            [
                "[#][#][#]\n" .
                "[#]   [#]\n" .
                "[#]...[#]",
                'Not a wall at line 3 column 4',
            ],
            [
                "[#](i)[#]\n" .
                "(i)   [#]\n" .
                "[#][#][#]",
                'Duplicate entrance: line 2 column 1',
            ],
            [
                "[#][#][#]\n" .
                "(i)   (i)\n" .
                "[#][#][#]",
                'Duplicate entrance: line 2 column 7',
            ],
            [
                "[#](E)[#]\n" .
                "(E)   [#]\n" .
                "[#][#][#]",
                'Duplicate exit: line 2 column 1',
            ],
            [
                "[#][#][#]\n" .
                "(E)   (E)\n" .
                "[#][#][#]",
                'Duplicate exit: line 2 column 7',
            ],
            [
                "[#][#][#]\n" .
                "[#]   ...\n" .
                "[#][#][#]",
                'Not a wall at line 2 column 7',
            ],

            // validate static chars
            [
                "...[#][#]\n" .
                "[#]   [#]\n" .
                "[#][#][#]",
                'Not a wall at line 1 column 1',
            ],
            [
                "[#][#]...[#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a wall at line 1 column 7',
            ],
            [
                "[#][#][#][#]...\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a wall at line 1 column 13',
            ],
            [
                "[#][#][#]\n" .
                "[#]   [#]\n" .
                "...[#][#]\n" .
                "[#]   [#]\n" .
                "[#][#][#]",
                'Not a wall at line 3 column 1',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#]...[#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a wall at line 3 column 7',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#]...\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a wall at line 3 column 13',
            ],
            [
                "[#][#][#]\n" .
                "[#]   [#]\n" .
                "[#][#][#]\n" .
                "[#]   [#]\n" .
                "...[#][#]",
                'Not a wall at line 5 column 1',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#]...[#][#]",
                'Not a wall at line 5 column 7',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#]...",
                'Not a wall at line 5 column 13',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#] ! [#]   [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a space at line 2 column 4',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#] ! [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a space at line 2 column 10',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#] ! [#]   [#]\n" .
                "[#][#][#][#][#]",
                'Not a space at line 4 column 4',
            ],
            [
                "[#][#][#][#][#]\n" .
                "[#]   [#]   [#]\n" .
                "[#][#][#][#][#]\n" .
                "[#]   [#] ! [#]\n" .
                "[#][#][#][#][#]",
                'Not a space at line 4 column 10',
            ],

            // detect inner ways
            [
                "[#][#][#][#][#][#][#]\n" .
                "[#]   [#]         [#]\n" .
                "[#][#][#]   [#]...[#]\n" .
                "[#]   [#]   [#]   [#]\n" .
                "[#][#][#][#][#][#][#]",
                'Not a wall at line 3 column 16',
            ],
            [
                "[#][#][#][#][#][#][#]\n" .
                "[#]   [#]         [#]\n" .
                "[#][#][#]   [#][#][#]\n" .
                "[#]   [#]   ...   [#]\n" .
                "[#][#][#][#][#][#][#]",
                'Not a wall at line 4 column 13',
            ],
        ];
    }

    public function testRandomReimport()
    {
        $maze = (new Generator(new Config(30, 20, 5, '')))->generate();

        $options = [
            'wall' => '[#]',
            'in' => '(i)',
            'out' => '(E)',
        ];

        $exporter = new TextExporter();
        $exporter->configureExport($options);
        $text = $exporter->exportMaze($maze);

        $importer = new TextImporter();
        $importer->configureImport($options);
        $importedMaze = $importer->importMaze($text);

        $this->assertEquals($maze, $importedMaze, 'Imported maze must be equal');
    }
}
