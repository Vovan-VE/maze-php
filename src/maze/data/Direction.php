<?php

namespace VovanVE\MazeProject\maze\data;

class Direction
{
    public const TOP = 1;
    public const RIGHT = 2;
    public const BOTTOM = 3;
    public const LEFT = 4;

    public static function random(): int
    {
        return \mt_rand(1, 4);
    }

    public static function next(int $from): int
    {
        return $from % 4 + 1;
    }

    public static function opposite(int $from): int
    {
        return ($from + 1) % 4 + 1;
    }

    public static function adjacentCoords(int $x, int $y, int $direction): array
    {
        $nextX = $x;
        $nextY = $y;
        switch ($direction) {
            case self::TOP:
                $nextY--;
                break;

            case self::RIGHT:
                $nextX++;
                break;

            case self::BOTTOM:
                $nextY++;
                break;

            case self::LEFT:
                $nextX--;
                break;

            default:
                throw new \InvalidArgumentException('Invalid direction');
        }

        return [$nextX, $nextY];
    }
}
