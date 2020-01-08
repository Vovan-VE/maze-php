<?php

namespace VovanVE\MazeProject\maze;

use VovanVE\MazeProject\maze\export\MazeExporterInterface;
use VovanVE\MazeProject\maze\export\TextExporter;

class Exporters
{
    public const F_TEXT = 'text';

    private const CLASSES = [
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
