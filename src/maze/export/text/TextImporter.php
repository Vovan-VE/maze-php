<?php

namespace VovanVE\MazeProject\maze\export\text;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\MazeImporterInterface;

class TextImporter extends TextBaseConfig implements MazeImporterInterface
{
    public function configureImport(array $options): void
    {
        $this->configureBase($options);
    }

    public function importMaze(string $input): Maze
    {
        $charsCount = \mb_strlen($this->wall, 'UTF-8');
        $space = \str_repeat(' ', $charsCount);

        $lines = $this->splitIntoLines($input);
        $height = $this->detectHeight($lines);
        $width = $this->detectWidth($lines, $charsCount);

        $cells = $this->splitIntoMatrix($lines, $charsCount);
        unset($lines);

        $maze = new Maze($width, $height);
        $this->detectDoors($maze, $cells, $charsCount);
        $this->validateStaticChars($maze, $cells, $space, $charsCount);
        $this->detectInnerWays($maze, $cells, $space, $charsCount);

        return $maze;
    }

    /**
     * @param string $input
     * @return string[]
     */
    private function splitIntoLines(string $input): array
    {
        $lines = \preg_split('/\\R/u', $input);
        \assert(false !== $lines, 'Unexpected split failure');

        while ($lines && '' === $lines[\count($lines) - 1]) {
            \array_pop($lines);
        }

        return $lines;
    }

    private function detectHeight(array $lines): int
    {
        $lineNum = \count($lines);
        if ($lineNum < 3 || 0 === $lineNum % 2) {
            throw new \InvalidArgumentException(
                'Invalid lines count: must be odd number of lines, ' .
                'and at least 3 lines'
            );
        }
        $height = ($lineNum - 1) / 2;
        \assert(\is_int($height) && $height > 0, 'Incorrect height calculated');

        return $height;
    }

    private function detectWidth(array $lines, int $cellChars): int
    {
        [$first] = $lines;
        $lineLength = \mb_strlen($first, 'UTF-8');
        if ($lineLength % $cellChars) {
            throw new \InvalidArgumentException(
                "Invalid line length: must be exact number of repeats of wall" .
                " chars count: $cellChars * n expected, but $lineLength given"
            );
        }

        $places = $lineLength / $cellChars;
        if ($places < 3 || 0 === $places % 2) {
            throw new \InvalidArgumentException(
                "Invalid line length: must be odd number of repeats of wall" .
                " chars count: $cellChars * (2n+1), where n>=1"
            );
        }
        $width = ($places - 1) / 2;
        \assert(\is_int($width) && $width > 0, 'Incorrect width calculated');

        for ($n = 1, $L = \count($lines); $n < $L; $n++) {
            if ($lineLength !== \mb_strlen($lines[$n], 'UTF-8')) {
                throw new \InvalidArgumentException(
                    // REFACT: PHP >= 8: unnecessary parens
                    "Invalid line length: line " . ($n + 1) .
                    " must be $lineLength chars"
                );
            }
        }

        return $width;
    }

    private function splitIntoMatrix(array $lines, int $cellChars): array
    {
        $matrix = [];

        $re = sprintf('/\\G.{1,%d}/us', $cellChars);
        foreach ($lines as $line) {
            // REFACT: PHP >= 7.4: \mb_str_split()
            \preg_match_all($re, $line, $m);
            $matrix[] = $m[0];
        }

        return $matrix;
    }

    private function detectDoors(Maze $maze, array $cells, int $cellChars): void
    {
        $inStr = $this->repeatStringToLength($this->in, $cellChars);
        $outStr = $this->repeatStringToLength($this->out, $cellChars);

        // top and bottom outer wall
        foreach ([0, $maze->getHeight()] as $y) {
            $cy = $y * 2;
            for ($x = $maze->getWidth(); $x-- > 0; ) {
                $cx = $x * 2 + 1;
                $cell = $cells[$cy][$cx];
                if ($cell === $inStr) {
                    if ($maze->getEntrance() !== null) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Duplicate entrance: line ' . ($cy + 1) .
                            ' column ' . ($cx * $cellChars + 1)
                        );
                    }
                    $maze->setEntrance(
                        $y ? Direction::BOTTOM : Direction::TOP,
                        $x
                    );
                } elseif ($cell === $outStr) {
                    if ($maze->getExit() !== null) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Duplicate exit: line ' . ($cy + 1) .
                            ' column ' . ($cx * $cellChars + 1)
                        );
                    }
                    $maze->setExit(
                        $y ? Direction::BOTTOM : Direction::TOP,
                        $x
                    );
                } elseif ($cell !== $this->wall) {
                    throw new \InvalidArgumentException(
                        // REFACT: PHP >= 8: unnecessary parens
                        'Not a wall at line ' . ($cy + 1) .
                        ' column ' . ($cx * $cellChars + 1)
                    );
                }
            }
        }

        // left and right outer wall
        foreach ([0, $maze->getWidth()] as $x) {
            $cx = $x * 2;
            for ($y = $maze->getHeight(); $y-- > 0; ) {
                $cy = $y * 2 + 1;
                $cell = $cells[$cy][$cx];
                if ($cell === $inStr) {
                    if ($maze->getEntrance() !== null) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Duplicate entrance: line ' . ($cy + 1) .
                            ' column ' . ($cx * $cellChars + 1)
                        );
                    }
                    $maze->setEntrance(
                        $x ? Direction::RIGHT : Direction::LEFT,
                        $y
                    );
                } elseif ($cell === $outStr) {
                    if ($maze->getExit() !== null) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Duplicate exit: line ' . ($cy + 1) .
                            ' column ' . ($cx * $cellChars + 1)
                        );
                    }
                    $maze->setExit(
                        $x ? Direction::RIGHT : Direction::LEFT,
                        $y
                    );
                } elseif ($cell !== $this->wall) {
                    throw new \InvalidArgumentException(
                        // REFACT: PHP >= 8: unnecessary parens
                        'Not a wall at line ' . ($cy + 1) .
                        ' column ' . ($cx * $cellChars + 1)
                    );
                }
            }
        }
    }

    private function validateStaticChars(
        Maze $maze,
        array $cells,
        string $space,
        int $cellChars
    ): void {
        for ($y = 0; $y <= $maze->getHeight(); $y++) {
            $cy = $y * 2;
            for ($x = 0; $x <= $maze->getWidth(); $x++) {
                $cx = $x * 2;
                if ($this->wall !== $cells[$cy][$cx]) {
                    throw new \InvalidArgumentException(
                        // REFACT: PHP >= 8: unnecessary parens
                        'Not a wall at line ' . ($cy + 1) .
                        ' column ' . ($cx * $cellChars + 1)
                    );
                }
                if ($x > 0 && $y > 0 && $space !== $cells[$cy - 1][$cx - 1]) {
                    throw new \InvalidArgumentException(
                        'Not a space at line ' . $cy .
                        ' column ' . (($cx - 1) * $cellChars + 1)
                    );
                }
            }
        }
    }

    private function detectInnerWays(
        Maze $maze,
        array $cells,
        string $space,
        int $cellChars
    ): void {
        for ($y = $maze->getHeight(); $y-- > 0; ) {
            $cy = $y * 2;
            for ($x = $maze->getWidth(); $x-- > 0; ) {
                $cx = $x * 2;

                if ($y > 0) {
                    $top = $cells[$cy][$cx + 1];
                    if ($top === $space) {
                        $maze->removeWalls($x, $y, Direction::TOP);
                    } elseif ($top !== $this->wall) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Not a wall at line ' . ($cy + 1) .
                            ' column ' . (($cx + 1) * $cellChars + 1)
                        );
                    }
                }
                if ($x > 0) {
                    $left = $cells[$cy + 1][$cx];
                    if ($left === $space) {
                        $maze->removeWalls($x, $y, Direction::LEFT);
                    } elseif ($left !== $this->wall) {
                        throw new \InvalidArgumentException(
                            // REFACT: PHP >= 8: unnecessary parens
                            'Not a wall at line ' . ($cy + 2) .
                            ' column ' . ($cx * $cellChars + 1)
                        );
                    }
                }
            }
        }
    }
}
