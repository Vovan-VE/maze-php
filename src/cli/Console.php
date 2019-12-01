<?php

namespace VovanVE\MazeProject\cli;

class Console
{
    public static function stderr(...$values): void
    {
        foreach ($values as $value) {
            if (false === fwrite(\STDERR, (string)$value)) {
                throw new \RuntimeException('Cannot write to STDERR');
            }
        }
    }
}
