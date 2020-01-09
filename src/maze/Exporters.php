<?php

namespace VovanVE\MazeProject\maze;

use VovanVE\MazeProject\maze\export\JsonExporter;
use VovanVE\MazeProject\maze\export\MazeExporterInterface;
use VovanVE\MazeProject\maze\export\TextExporter;

class Exporters
{
    public const F_JSON = 'json';
    public const F_TEXT = 'text';

    private const CLASSES = [
        self::F_JSON => JsonExporter::class,
        self::F_TEXT => TextExporter::class,
    ];

    public static function hasFormat(string $format): bool
    {
        return isset(self::CLASSES[$format]);
    }

    public static function getExporter(string $format): MazeExporterInterface
    {
        $class = self::CLASSES[$format] ?? null;
        if (null === $class) {
            throw new \InvalidArgumentException('Unknown format name');
        }
        return new $class();
    }
}
