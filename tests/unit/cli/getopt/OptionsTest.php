<?php

namespace VovanVE\MazeProject\tests\unit\cli\getopt;

use VovanVE\MazeProject\cli\getopt\Options;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class OptionsTest extends BaseTestCase
{
    public function testGetters()
    {
        $options = [
            'switch' => null,
            'option' => 'value',
        ];
        $mixed = ['lorem', 'ipsum', 'dolor'];
        $rest = ['foo', 'bar', 'lol'];

        $opt = new Options($options, $mixed, $rest);

        $this->assertEquals($options, $opt->getOptions());

        $this->assertTrue($opt->hasOpt('switch'));
        $this->assertTrue($opt->hasOpt('option'));
        $this->assertFalse($opt->hasOpt('omitted'));

        $this->assertTrue($opt->hasOpt('switch', 'option'));
        $this->assertTrue($opt->hasOpt('switch', 'omitted'));
        $this->assertTrue($opt->hasOpt('option', 'omitted'));
        $this->assertFalse($opt->hasOpt('omitted', 'unknown'));

        $this->assertEquals(null, $opt->getOpt('switch'));
        $this->assertEquals(null, $opt->getOpt('switch', 42));
        $this->assertEquals('value', $opt->getOpt('option'));
        $this->assertEquals('value', $opt->getOpt('option', 42));
        $this->assertEquals(null, $opt->getOpt('missing'));
        $this->assertEquals(42, $opt->getOpt('missing', 42));

        $this->assertEquals($mixed, $opt->getMixedValues());
        $this->assertEquals($rest, $opt->getRestValues());
    }
}
