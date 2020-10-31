<?php

namespace VovanVE\MazeProject\tests\unit\maze;

use VovanVE\MazeProject\maze\format\MazeExporterInterface;
use VovanVE\MazeProject\maze\format\MazeImporterInterface;
use VovanVE\MazeProject\maze\Formats;
use VovanVE\MazeProject\tests\helpers\BaseTestCase;

class FormatsTest extends BaseTestCase
{
    public function testHasExporter()
    {
        $this->assertTrue(Formats::hasExporter(Formats::F_JSON));
        $this->assertTrue(Formats::hasExporter(Formats::F_TEXT));

        $this->assertFalse(Formats::hasExporter('*UNKNOWN'));
    }

    public function testHasImporter()
    {
        $this->assertTrue(Formats::hasImporter(Formats::F_JSON));
        $this->assertTrue(Formats::hasImporter(Formats::F_TEXT));

        $this->assertFalse(Formats::hasImporter('*UNKNOWN'));
    }

    public function testGetExporter()
    {
        $this->assertInstanceOf(
            MazeExporterInterface::class,
            Formats::getExporter(Formats::F_JSON)
        );
        $this->assertInstanceOf(
            MazeExporterInterface::class,
            Formats::getExporter(Formats::F_TEXT)
        );
    }

    public function testGetImporter()
    {
        $this->assertInstanceOf(
            MazeImporterInterface::class,
            Formats::getImporter(Formats::F_JSON)
        );
        $this->assertInstanceOf(
            MazeImporterInterface::class,
            Formats::getImporter(Formats::F_TEXT)
        );
    }

    public function testGetExporterFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format name');

        Formats::getExporter('*UNKNOWN');
    }

    public function testGetImporterFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format name');

        Formats::getImporter('*UNKNOWN');
    }
}
