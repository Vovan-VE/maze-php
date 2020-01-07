<?php

namespace VovanVE\MazeProject\tests\unit\cli\getopt;

use VovanVE\MazeProject\cli\getopt\OptionsHandlers;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class OptionsHandlersTest extends BaseTestCase
{
    public function testCounter()
    {
        $counter = OptionsHandlers::getCounter();

        $first = $counter(null, true);
        $second = $counter($first, true);
        $third = $counter($second, true);

        $this->assertEquals(1, $first);
        $this->assertEquals(2, $second);
        $this->assertEquals(3, $third);
    }

    public function testMapper()
    {
        $mapper = OptionsHandlers::getMapper();

        $first = $mapper(null, 'lorem=foo  bar=97');
        $second = $mapper($first, 'ipsum=');
        $third = $mapper($second, 'dolor');
        $fourth = $mapper($third, 'lorem=42');

        $this->assertEquals(
            [
                'lorem' => 'foo  bar=97',
            ],
            $first
        );
        $this->assertEquals(
            [
                'lorem' => 'foo  bar=97',
                'ipsum' => '',
            ],
            $second
        );
        $this->assertEquals(
            [
                'lorem' => 'foo  bar=97',
                'ipsum' => '',
                'dolor' => true,
            ],
            $third
        );
        $this->assertEquals(
            [
                'lorem' => '42',
                'ipsum' => '',
                'dolor' => true,
            ],
            $fourth
        );

        $default = new \stdClass();
        $mapper = OptionsHandlers::getMapper($default);

        $map = $mapper(null, 'foo');
        $this->assertSame(['foo' => $default], $map);
    }
}
