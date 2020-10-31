<?php

namespace VovanVE\MazeProject\maze;

use VovanVE\MazeProject\maze\format\json\JsonExporter;
use VovanVE\MazeProject\maze\format\json\JsonImporter;
use VovanVE\MazeProject\maze\format\MazeExporterInterface;
use VovanVE\MazeProject\maze\format\MazeImporterInterface;
use VovanVE\MazeProject\maze\format\text\TextExporter;
use VovanVE\MazeProject\maze\format\text\TextImporter;

class Formats
{
    public const F_JSON = 'json';
    public const F_TEXT = 'text';

    private const EXPORTERS = [
        self::F_JSON => JsonExporter::class,
        self::F_TEXT => TextExporter::class,
    ];
    private const IMPORTERS = [
        self::F_JSON => JsonImporter::class,
        self::F_TEXT => TextImporter::class,
    ];

    public static function hasExporter(string $format): bool
    {
        return isset(self::EXPORTERS[$format]);
    }

    public static function hasImporter(string $format): bool
    {
        return isset(self::IMPORTERS[$format]);
    }

    public static function getExporter(string $format): MazeExporterInterface
    {
        $class = self::EXPORTERS[$format] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException('Unknown format name');
        }
        return new $class();
    }

    public static function getImporter(string $format): MazeImporterInterface
    {
        $class = self::IMPORTERS[$format] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException('Unknown format name');
        }
        return new $class();
    }
}
