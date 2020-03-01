<?php

namespace VovanVE\MazeProject\maze\export\json;

use VovanVE\MazeProject\maze\data\Direction;
use VovanVE\MazeProject\maze\data\Maze;
use VovanVE\MazeProject\maze\export\MazeImporterInterface;

class JsonImporter implements MazeImporterInterface
{
    public function importMaze(string $input): Maze
    {
        [
            'width' => $width,
            'height' => $height,
            'in' => $in,
            'out' => $out,
            'cells' => $cells,
        ] = $this->decodeInput($input);

        $maze = new Maze($width, $height);
        if (null !== $in) {
            $maze->setEntrance(...$in);
        }
        if (null !== $out) {
            $maze->setExit(...$out);
        }

        $lastX = $width - 1;
        $lastY = $height - 1;
        foreach ($cells as $y => $line) {
            for ($x = 0; $x < $width; $x++) {
                $char = $line[$x];
                switch ($char) {
                    case '0':
                        if ($x !== $lastX) {
                            $maze->removeWalls($x, $y, Direction::RIGHT);
                        }
                        if ($y !== $lastY) {
                            $maze->removeWalls($x, $y, Direction::BOTTOM);
                        }
                        break;

                    case '1':
                        if ($y !== $lastY) {
                            $maze->removeWalls($x, $y, Direction::BOTTOM);
                        }
                        break;

                    case '2':
                        if ($x !== $lastX) {
                            $maze->removeWalls($x, $y, Direction::RIGHT);
                        }
                        break;
                }
            }
        }

        return $maze;
    }

    protected function decodeInput(string $input): array
    {
        // REFACT: PHP >= 7.3: JSON_THROW_ON_ERROR
        $data = \json_decode($input, true);
        if (null === $data && !\preg_match('/^\\s*null\\s*/uD', $input)) {
            throw new \InvalidArgumentException('Invalid JSON');
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                'Invalid JSON data - not an object'
            );
        }

        $width = $this->validateInt($data, 'width', 'width', 1);
        $height = $this->validateInt($data, 'height', 'height', 1);

        $in = $this->validateDoorOptional($data, 'in', 'in', $width, $height);
        $out = $this->validateDoorOptional($data, 'out', 'out', $width, $height);

        $cells = $this->validateCells($data, 'cells', 'cells', $width, $height);

        return [
            'width' => $width,
            'height' => $height,
            'in' => $in,
            'out' => $out,
            'cells' => $cells,
        ];
    }

    protected function validateDoorOptional(
        array $data,
        string $field,
        string $path,
        int $width,
        int $height
    ): ?array {
        // really absent or is null
        if (!isset($data[$field])) {
            return null;
        }

        $d = $data[$field];
        if (!is_array($d) || count($d) !== 2 || [0, 1] !== array_keys($d)) {
            throw new \InvalidArgumentException(
                "Value of `$path` must be an array of exactly two elements"
            );
        }

        $side = $this->validateDirection($d, 0, "{$path}[0]");
        $max = ($side === Direction::TOP || $side == Direction::BOTTOM)
            ? $width - 1
            : $height - 1;
        $offset = $this->validateInt($d, 1, "{$path}[1]", 0, $max);

        return [$side, $offset];
    }

    protected function validateCells(
        array $data,
        string $field,
        string $path,
        int $width,
        int $height
    ): ?array {
        $this->fieldMustExist($data, $field, $path);

        $lastY = $height - 1;
        $cells = $data[$field];
        if (
            !\is_array($cells)
            || \count($cells) !== $height
            || \array_keys($cells) !== \range(0, $lastY)
        ) {
            throw new \InvalidArgumentException(
                "Value of `$path` must be array with `$height` items"
            );
        }
        foreach ($cells as $i => $line) {
            if (!\is_string($line) || \strlen($line) !== $width) {
                throw new \InvalidArgumentException(
                    "Value of `{$path}[$i]` must be string of `$width` digits"
                );
            }

            if ($i < $lastY) {
                if (!preg_match('/^[0-3]*[02]$/D', $line)) {
                    throw new \InvalidArgumentException(
                        "String in `{$path}[$i]` can to contain only " .
                        "`0`..`3` digits, and the last digit can be only " .
                        "`0` or `2`"
                    );
                }
            } else {
                if (!preg_match('/^[01]*0$/D', $line)) {
                    throw new \InvalidArgumentException(
                        "Last string in `{$path}[$i]` can to contain only " .
                        "`0` and `1` digits, and the last digit can be only `0`"
                    );
                }
            }
        }

        return $cells;
    }

    protected function validateInt(
        array $data,
        $field,
        string $path,
        ?int $min = null,
        ?int $max = null
    ): int {
        $this->fieldMustExist($data, $field, $path);

        $value = $data[$field];
        if (!\is_int($value)) {
            throw new \InvalidArgumentException(
                "Value of `$path` must be integer"
            );
        }
        if (null !== $min && $value < $min) {
            throw new \InvalidArgumentException(
                "Value of `$path` cannot be less than `$min`"
            );
        }
        if (null !== $max && $value > $max) {
            throw new \InvalidArgumentException(
                "Value of `$path` cannot be greater than `$max`"
            );
        }
        return $value;
    }

    protected function validateDirection(
        array $data,
        $field,
        string $path
    ): int {
        $this->fieldMustExist($data, $field, $path);

        $value = $data[$field];
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(
                "Value of `$path` must be string"
            );
        }

        switch ($value) {
            case 'top':
                return Direction::TOP;

            case 'right':
                return Direction::RIGHT;

            case 'bottom':
                return Direction::BOTTOM;

            case 'left':
                return Direction::LEFT;

            default:
                throw new \InvalidArgumentException(
                    "Value of `$path` must be " .
                    "\"top\", \"right\", \"bottom\" or \"left\""
                );
        }
    }

    /**
     * @param array $data
     * @param $field
     * @param string $path
     */
    private function fieldMustExist(array $data, $field, string $path): void
    {
        if (!\array_key_exists($field, $data)) {
            throw new \InvalidArgumentException(
                "The field `$path` did not set"
            );
        }
    }
}
