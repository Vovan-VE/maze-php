<?php

namespace VovanVE\MazeProject\tests\unit\cli\getopt;

use VovanVE\MazeProject\cli\getopt\Options;
use VovanVE\MazeProject\cli\getopt\OptionsHandlers;
use VovanVE\MazeProject\cli\getopt\OptionsParser;
use VovanVE\MazeProject\cli\getopt\InvalidOptionException;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class OptionsParserTest extends BaseTestCase
{
    /**
     * @param array $long
     * @param string $error
     * @dataProvider dataCreateFail
     */
    public function testCreateFail(array $long, string $error)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($error);

        new OptionsParser($long);
    }

    public function dataCreateFail()
    {
        return [
            [
                ['a', '', 'b'],
                "Bad option name ''",
            ],
            [
                ['a', 'x||y', 'b'],
                "Bad option name ''",
            ],
            [
                ['a', '-', 'b'],
                "Bad option name '-'",
            ],
            [
                ['a', '-|b'],
                "Bad option name '-'",
            ],
            [
                ['a', 'b|-'],
                "Bad option name '-'",
            ],
            [
                ['a', 'b', 'a'],
                "Duplicate option 'a'",
            ],
            [
                ['a', 'b', 'a:'],
                "Duplicate option 'a'",
            ],
            [
                ['a:', 'b', 'a'],
                "Duplicate option 'a'",
            ],
            [
                ['a', 'a::'],
                "Duplicate option 'a'",
            ],

            [
                ['foo', 'bar', 'foo'],
                "Duplicate option 'foo'",
            ],
            [
                ['foo', 'bar', 'foo:'],
                "Duplicate option 'foo'",
            ],
            [
                ['foo:', 'bar', 'foo'],
                "Duplicate option 'foo'",
            ],
            [
                ['foo', 'bar', 'foo::'],
                "Duplicate option 'foo'",
            ],
        ];
    }

    public function testCreate()
    {
        $o = new OptionsParser(
            [
                'a',
                'foo',
                'b|bar',
                'c:',
                'baz:',
                'd|q|qwe:',
                'e::',
                'lol::',
                'sit|f::'
            ]
        );

        $this->assertEquals(
            $o->getTypes(),
            [
                'a' => OptionsParser::V_NO,
                'foo' => OptionsParser::V_NO,
                'b' => OptionsParser::V_NO,
                'c' => OptionsParser::V_REQUIRED,
                'baz' => OptionsParser::V_REQUIRED,
                'd' => OptionsParser::V_REQUIRED,
                'e' => OptionsParser::V_OPTIONAL,
                'lol' => OptionsParser::V_OPTIONAL,
                'sit' => OptionsParser::V_OPTIONAL,
            ]
        );
        $this->assertEquals(
            $o->getShortAlias(),
            [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'd' => 'd',
                'q' => 'd',
                'e' => 'e',
                'f' => 'sit',
            ]
        );
        $this->assertEquals(
            $o->getLongAlias(),
            [
                'foo' => 'foo',
                'bar' => 'b',
                'baz' => 'baz',
                'qwe' => 'd',
                'lol' => 'lol',
                'sit' => 'sit',
            ]
        );
    }

    /**
     * @param array $long
     * @param array $input
     * @param Options $result
     * @dataProvider dataParse
     */
    public function testParse(array $long, array $input, Options $result)
    {
        $getopt = new OptionsParser($long);
        $this->assertEquals($result, $getopt->parse($input));
    }

    public function dataParse()
    {
        return [
            [
                [],
                [],
                new Options(),
            ],

            [
                ['a', 'b', 'c', 'd', 'e'],
                ['-a', 'foo', '-b', '', '-cd', '-', 'bar'],
                new Options(
                    [
                        'a' => true,
                        'b' => true,
                        'c' => true,
                        'd' => true,
                    ],
                    ['foo', '', '-', 'bar']
                ),
            ],
            [
                ['a:', 'b:', 'c:', 'd', 'e'],
                ['-a', 'foo', '-b', '', '-cde', '-de', '-', 'bar'],
                new Options(
                    [
                        'a' => 'foo',
                        'b' => '',
                        'c' => 'de',
                        'd' => true,
                        'e' => true,
                    ],
                    ['-', 'bar']
                ),
            ],
            [
                ['a::', 'b::', 'c', 'd::', 'e'],
                ['-a', 'foo', '-bcd', '', '-cd', '-', 'bar'],
                new Options(
                    [
                        'a' => '',
                        'b' => 'cd',
                        'c' => true,
                        'd' => '',
                    ],
                    ['foo', '', '-', 'bar']
                ),
            ],

            [
                ['foo', 'bar', 'lol', 'baz'],
                ['--foo', 'a', '--bar', '', '--lol', '-', 'bar'],
                new Options(
                    [
                        'foo' => true,
                        'bar' => true,
                        'lol' => true,
                    ],
                    ['a', '', '-', 'bar']
                ),
            ],
            [
                ['foo:', 'bar:', 'lol:', 'baz:'],
                ['--foo', '-a', '--bar', '', '--lol', '-', 'qux'],
                new Options(
                    [
                        'foo' => '-a',
                        'bar' => '',
                        'lol' => '-',
                    ],
                    ['qux']
                ),
            ],
            [
                ['foo:', 'bar:', 'lol:', 'baz:'],
                ['--foo=', 'a', '--bar=hello', 'qux'],
                new Options(
                    [
                        'foo' => '',
                        'bar' => 'hello',
                    ],
                    ['a', 'qux']
                ),
            ],
            [
                ['foo::', 'bar::', 'lol::', 'baz::'],
                ['--foo', 'a', '--bar', '--lol', 'qux'],
                new Options(
                    [
                        'foo' => true,
                        'bar' => true,
                        'lol' => true,
                    ],
                    ['a', 'qux']
                ),
            ],
            [
                ['foo::', 'bar::', 'lol::', 'baz::'],
                ['--foo=', 'a', '--bar=hello', 'qux'],
                new Options(
                    [
                        'foo' => '',
                        'bar' => 'hello',
                    ],
                    ['a', 'qux']
                ),
            ],

            [
                ['a|foo:', 'bar|b:'],
                [
                    '--bar=first',
                    '-a10',
                    '--foo=20',
                    '-bsecond',
                    '--',
                    '-a=30',
                    '--bar=third'
                ],
                new Options(
                    [
                        'a' => '20',
                        'bar' => 'second',
                    ],
                    [],
                    ['-a=30', '--bar=third']
                ),
            ],
            [
                ['a', 'foo'],
                ['foo', 'a', '--', '-a', '--foo', 'bar'],
                new Options(
                    [],
                    ['foo', 'a'],
                    ['-a', '--foo', 'bar']
                ),
            ],
        ];
    }

    /**
     * @param array $long
     * @param array $input
     * @param string $error
     * @dataProvider dataParseFail
     */
    public function testParseFail(array $long, array $input, string $error)
    {
        $getopt = new OptionsParser($long);

        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage($error);

        $getopt->parse($input);
    }

    public function dataParseFail()
    {
        return [
            [
                ['a', 'b'],
                ['-a', '-ba', '-acb'],
                'unrecognized key: `-c`',
            ],
            [
                ['a:', 'b:', 'c:', 'd:'],
                ['-a10', '-bcd', '-c', '20', '-d'],
                'key `-d` must be used with value',
            ],

            [
                ['foo', 'bar'],
                ['--foo', '--bar', '--lol', '--bar'],
                'unrecognized option: `--lol`',
            ],
            [
                ['foo', 'bar'],
                ['--foo', '--bar='],
                'option `--bar` cannot be used with value',
            ],
            [
                ['foo', 'bar'],
                ['--foo', '--bar=20', 'hello'],
                'option `--bar` cannot be used with value',
            ],
            [
                ['foo:', 'bar:'],
                ['--foo', 'hello', '--bar'],
                'option `--bar` must be used with value',
            ],
        ];
    }

    public function testBypassUnknown()
    {
        $getopt = new OptionsParser(['h|help']);
        $this->assertFalse($getopt->getBypassUnknown());
        $this->assertSame($getopt, $getopt->setBypassUnknown(false));
        $this->assertSame($getopt, $getopt->setBypassUnknown(true));
        $this->assertTrue($getopt->getBypassUnknown());
    }

    /**
     * @param array $input
     * @param Options $result
     * @dataProvider dataParseBypassUnknown
     */
    public function testParseBypassUnknown(array $input, Options $result)
    {
        $getopt = (new OptionsParser(['h|help', 'HELP|H']))
            ->setBypassUnknown(true);

        $this->assertEquals($result, $getopt->parse($input));
    }

    public function dataParseBypassUnknown()
    {
        return [
            [
                ['-x', 'foo', '--what', 'bar', '--', 'bar', '-h', 'lol'],
                new Options(
                    [],
                    ['-x', 'foo', '--what', 'bar'],
                    ['bar', '-h', 'lol']
                ),
            ],

            [
                ['-h', '-x', 'foo', '--what', 'bar', '--', 'bar', '-h', 'lol'],
                new Options(
                    ['h' => true],
                    ['-x', 'foo', '--what', 'bar'],
                    ['bar', '-h', 'lol']
                ),
            ],
            [
                ['-x', '-h', 'foo', '--what', 'bar', '--', 'bar', '-h', 'lol'],
                new Options(
                    ['h' => true],
                    ['-x', 'foo', '--what', 'bar'],
                    ['bar', '-h', 'lol']
                ),
            ],
            [
                ['-x', 'foo', '--what', 'bar', '-h', '--', 'bar', '-h', 'lol'],
                new Options(
                    ['h' => true],
                    ['-x', 'foo', '--what', 'bar'],
                    ['bar', '-h', 'lol']
                ),
            ],

            [
                ['--HELP', '--foo', 'foo', '-x', 'bar', '--', 'bar', '-H'],
                new Options(
                    ['HELP' => true],
                    ['--foo', 'foo', '-x', 'bar'],
                    ['bar', '-H']
                ),
            ],
            [
                ['--foo', 'foo', '--HELP', '-x', 'bar', '--', 'bar', '-H'],
                new Options(
                    ['HELP' => true],
                    ['--foo', 'foo', '-x', 'bar'],
                    ['bar', '-H']
                ),
            ],
            [
                ['--foo', 'foo', '-x', 'bar', '--HELP', '--', 'bar', '-H'],
                new Options(
                    ['HELP' => true],
                    ['--foo', 'foo', '-x', 'bar'],
                    ['bar', '-H']
                ),
            ],
        ];
    }

    public function testHandlers()
    {
        $getopt = new OptionsParser([
            'v|verbose' => OptionsHandlers::getCounter(),
            'd|define:' => OptionsHandlers::getMapper(),
        ]);

        $this->assertEquals([], $getopt->parse([])->getOptions());

        $this->assertEquals(
            [
                'v' => 4,
                'd' => [
                    'foo' => 42,
                    'bar' => true,
                ],
            ],
            $getopt->parse(
                ['-v', '-d', 'foo=37', '-vvv', '-d', 'bar', '-dfoo=42']
            )->getOptions()
        );
    }
}
