<?php

namespace VovanVE\MazeProject\cli\getopt;

class OptionsHandlers
{
    public static function getCounter(): \Closure
    {
        return function (?int $prev) {
            return ($prev ?? 0) + 1;
        };
    }

    public static function getMapper($noValue = true): \Closure
    {
        return function (?array $map, $value) use ($noValue) {
            if (null === $map) {
                $map = [];
            }

            if (\is_string($value)) {
                $pair = \explode('=', $value, 2);
                $map[$pair[0]] = $pair[1] ?? $noValue;
            }

            return $map;
        };
    }
}
