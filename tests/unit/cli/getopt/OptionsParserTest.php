<?php

namespace VovanVE\MazeProject\tests\unit\cli\getopt;

use VovanVE\MazeProject\cli\getopt\Options;
use VovanVE\MazeProject\cli\getopt\OptionsParser;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class OptionsParserTest extends BaseTestCase
{
    /**
     * @param string $input
     * @param array $output
     * @dataProvider dataExpandShortDefinition
     */
    public function testExpandShortDefinition(string $input, array $output)
    {
        $this->assertEquals(
            \iterator_to_array(OptionsParser::expandShortDefinition($input)),
            $output
        );
    }

    public function dataExpandShortDefinition()
    {
        return [
            [
                '',
                [],
            ],
            [
                'a',
                ['a'],
            ],
            [
                'ab',
                ['a', 'b'],
            ],
            [
                'a:',
                ['a:'],
            ],
            [
                'a:b',
                ['a:', 'b'],
            ],
            [
                'a::',
                ['a::'],
            ],
            [
                'a::b',
                ['a::', 'b'],
            ],
            [
                'ab:',
                ['a', 'b:'],
            ],
            [
                'ab::',
                ['a', 'b::'],
            ],
            [
                'a:b:',
                ['a:', 'b:'],
            ],
            [
                'a::b:',
                ['a::', 'b:'],
            ],
            [
                'a:b::',
                ['a:', 'b::'],
            ],
            [
                'a::b::',
                ['a::', 'b::'],
            ],
        ];
    }

    /**
     * @param string $input
     * @param string $message
     * @dataProvider dataExpandShortDefinitionFail
     */
    public function testExpandShortDefinitionFail(
        string $input,
        string $message
    ) {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        \iterator_to_array(OptionsParser::expandShortDefinition($input));
    }

    public function dataExpandShortDefinitionFail()
    {
        return [
            [
                '-',
                "Bad key `-` at offset 0",
            ],
            [
                ':',
                "Bad key `:` at offset 0",
            ],
            [
                'a:::',
                "Bad key `:` at offset 3",
            ],
            [
                'abc:::',
                "Bad key `:` at offset 5",
            ],
        ];
    }

    /**
     * @param string $short
     * @param array $long
     * @param string $error
     * @dataProvider dataCreateFail
     */
    public function testCreateFail(string $short, array $long, string $error)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($error);

        new OptionsParser($short, $long);
    }

    public function dataCreateFail()
    {
        return [
            [
                'aba',
                [],
                "Duplicate option 'a'",
            ],
            [
                'aba:',
                [],
                "Duplicate option 'a'",
            ],
            [
                'a:ba',
                [],
                "Duplicate option 'a'",
            ],
            [
                'aa::',
                [],
                "Duplicate option 'a'",
            ],

            [
                '',
                ['foo', 'bar', 'foo'],
                "Duplicate option 'foo'",
            ],
            [
                '',
                ['foo', 'bar', 'foo:'],
                "Duplicate option 'foo'",
            ],
            [
                '',
                ['foo:', 'bar', 'foo'],
                "Duplicate option 'foo'",
            ],
            [
                '',
                ['foo', 'bar', 'foo::'],
                "Duplicate option 'foo'",
            ],

            [
                'abc',
                ['boo', 'a', 'foo', 'c'],
                'Some options duplicated in short and long forms: "a", "c"',
            ],
        ];
    }

    public function testCreate()
    {
        $o = new OptionsParser(
            'abc:d:e::f::',
            ['foo', 'bar', 'baz:', 'qwe:', 'lol::', 'sit::']
        );

        $this->assertEquals(
            $o->getShort(),
            [
                'a' => OptionsParser::V_NO,
                'b' => OptionsParser::V_NO,
                'c' => OptionsParser::V_REQUIRED,
                'd' => OptionsParser::V_REQUIRED,
                'e' => OptionsParser::V_OPTIONAL,
                'f' => OptionsParser::V_OPTIONAL,
            ]
        );

        $this->assertEquals(
            $o->getLong(),
            [
                'foo' => OptionsParser::V_NO,
                'bar' => OptionsParser::V_NO,
                'baz' => OptionsParser::V_REQUIRED,
                'qwe' => OptionsParser::V_REQUIRED,
                'lol' => OptionsParser::V_OPTIONAL,
                'sit' => OptionsParser::V_OPTIONAL,
            ]
        );
    }

    /**
     * @param string $short
     * @param array $long
     * @param array $input
     * @param Options $result
     * @dataProvider dataParse
     */
    public function testParse(
        string $short,
        array $long,
        array $input,
        Options $result
    ) {
        $getopt = new OptionsParser($short, $long);
        $this->assertEquals($result, $getopt->parse($input));
    }

    public function dataParse()
    {
        return [
            [
                '',
                [],
                [],
                new Options(),
            ],

            [
                'abcde',
                [],
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
                'a:b:c:de',
                [],
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
                'a::b::cd::e',
                [],
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
                '',
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
                '',
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
                '',
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
                '',
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
                '',
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
                'a:',
                ['foo:'],
                [
                    '--foo=first',
                    '-a10',
                    '-a20',
                    '--foo=second',
                    '--',
                    '-a=30',
                    '--foo=third'
                ],
                new Options(
                    [
                        'a' => '20',
                        'foo' => 'second',
                    ],
                    [],
                    ['-a=30', '--foo=third']
                ),
            ],
            [
                'a',
                ['foo'],
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
     * @param string $short
     * @param array $long
     * @param array $input
     * @param string $error
     * @dataProvider dataParseFail
     */
    public function testParseFail(
        string $short,
        array $long,
        array $input,
        string $error
    ) {
        $getopt = new OptionsParser($short, $long);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($error);

        $getopt->parse($input);
    }

    public function dataParseFail()
    {
        return [
            [
                'ab',
                [],
                ['-a', '-ba', '-acb'],
                'unrecognized key: `-c`',
            ],
            [
                'a:b:c:d:',
                [],
                ['-a10', '-bcd', '-c', '20', '-d'],
                'key `-d` must be used with value',
            ],

            [
                '',
                ['foo', 'bar'],
                ['--foo', '--bar', '--lol', '--bar'],
                'unrecognized option: `--lol`',
            ],
            [
                '',
                ['foo', 'bar'],
                ['--foo', '--bar='],
                'option `--bar` cannot be used with value',
            ],
            [
                '',
                ['foo', 'bar'],
                ['--foo', '--bar=20', 'hello'],
                'option `--bar` cannot be used with value',
            ],
            [
                '',
                ['foo:', 'bar:'],
                ['--foo', 'hello', '--bar'],
                'option `--bar` must be used with value',
            ],
        ];
    }
}
