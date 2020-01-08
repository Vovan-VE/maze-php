<?php

namespace VovanVE\MazeProject\tests\unit\maze;

use VovanVE\MazeProject\maze\export\MazeExporterInterface;
use VovanVE\MazeProject\maze\Exporters;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class ExportersTest extends BaseTestCase
{
    public function testHasFormat()
    {
        $this->assertTrue(Exporters::hasFormat(Exporters::F_TEXT));

        $this->assertFalse(Exporters::hasFormat('*UNKNOWN'));
    }

    public function testGetFormatter()
    {
        $this->assertInstanceOf(
            MazeExporterInterface::class,
            Exporters::getExporter(Exporters::F_TEXT)
        );
    }

    public function testGetFormatterFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format name');

        Exporters::getExporter('*UNKNOWN');
    }
}
