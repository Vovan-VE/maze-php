<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\cli\Console;
use VovanVE\MazeProject\cli\getopt\Options;
use VovanVE\MazeProject\cli\getopt\OptionsParser;
use VovanVE\MazeProject\maze\Config;
use VovanVE\MazeProject\maze\export\TextExporter;
use VovanVE\MazeProject\maze\Generator;

class GenerateCommand extends BaseCommand
{
    private const DEF_WIDTH = 30;
    private const DEF_HEIGHT = 10;
    private const DEF_BRANCH_LENGTH = 10;

    public function run(array $args): int
    {
        $opts = (new OptionsParser(
            [
                'W|width:',
                'H|height:',
                's|size:',
                'B|branch-length:',
                'F|format:'
            ]
        ))
            ->parse($args);

        $config = $this->getConfig($opts, $error);
        if (null === $config) {
            Console::stderr($error, \PHP_EOL);
            return 2;
        }

        $maze = (new Generator($config))->generate();

        echo (new TextExporter())->exportMaze($maze), \PHP_EOL;

        return 0;
    }

    public function getUsageHelp(): string
    {
        return <<<'_END'
maze [gen] [options]

Generate a maze and export it to stdout.

Since `gen` in the default command, the command name `gen` is optional.

Options:

    -W <WIDTH>, --width=<WIDTH>
        Maze width in number of CELLs. Default is `30`.

    -H <HEIGHT>, --height=<HEIGHT>
        Maze height in number of CELLs. Default is `10`.

        Notice about uppercase `-H` to not mix with `-h` which is "help".

    -s <SIZE>, --size=<SIZE>
        Alternative way to set both width and height at once. The `SIZE`
        must be in form `<WIDTH>x<HEIGHT>`. So, the default size is `30x10`.

    -B <BL>, --branch-length=<BL>
        The "branch length" option for generation. `BL` can be an integer > 1
        (a number of CELLs), string `max` (which is `WIDTH * HEIGHT`), or
        decimal from 0 to 1 as fraction of max (for example, `0.2` is
        `round(0.2 * W * H)`). Default is `10`.

    -f <FORMAT>, --format=<FORMAT>
        Output format. Can be one of `art`, `json` or `text`. The default is
        `art` to be human readable.

_END;
    }

    /**
     * @param Options $opts
     * @param string $error
     * @return Config|null
     */
    private function getConfig(Options $opts, &$error): ?Config
    {
        $width = self::DEF_WIDTH;
        $height = self::DEF_HEIGHT;
        $branchLength = self::DEF_BRANCH_LENGTH;
        // TODO: formats
        $format = 'art';

        if ($opts->hasOpt('s')) {
            if ($opts->hasOpt('W', 'H')) {
                $error = 'E: mixing `-s` (`--size`) and one of `-W` (`--width`) or `-H` (`--height`)';
                return null;
            }

            $size = $opts->getOpt('s');
            $parts = \explode('x', $size);
            if (2 !== \count($parts)) {
                $error = 'E: size must be in form `<WIDTH>x<HEIGHT>`, like `30x10`';
                return null;
            }
            [$widthStr, $heightStr] = $parts;
            if (!$this->validatePositiveInt($widthStr, $width, $error)) {
                $error = "E: <WIDTH> $error in `-s` (`--size`)";
                return null;
            }
            if (!$this->validatePositiveInt($heightStr, $height, $error)) {
                $error = "E: <HEIGHT> $error in `-s` (`--size`)";
                return null;
            }
        } else {
            $widthStr = $opts->getOpt('W');
            $heightStr = $opts->getOpt('H');
            if (null !== $widthStr && !$this->validatePositiveInt(
                    $widthStr,
                    $width,
                    $error
                )) {
                $error = "E: <WIDTH> $error in `-W` (`--width`)";
                return null;
            }
            if (null !== $heightStr && !$this->validatePositiveInt(
                    $heightStr,
                    $height,
                    $error
                )) {
                $error = "E: <HEIGHT> $error in `-H` (`--height`)";
                return null;
            }
        }

        if (
            $opts->hasOpt('B') &&
            !$this->validateBranchLength(
                $opts->getOpt('B'),
                $width * $height,
                $branchLength,
                $error
            )
        ) {
            $error = "E: <BL> $error in `-B` (`--branch-length`)";
            return null;
        }

        if ($opts->hasOpt('f')) {
            //$formatStr = $opts->getOpt('f');
            // TODO: formats
            // TODO: validate
            Console::stderr(
                'W! Format choise is not implemented yet',
                \PHP_EOL
            );
        }

        return new Config($width, $height, $branchLength, $format);
    }

    private function validateBranchLength(
        string $input,
        int $total,
        &$out,
        &$error
    ): bool {
        if ($input === 'max') {
            $out = $total;
            return true;
        }

        if ($this->validatePositiveInt($input, $int, $error)) {
            if ($int > $total) {
                $int = $total;
            }
            $out = $int;
            return true;
        }

        if (preg_match('/^0?\\.\\d+/', $input)) {
            $float = (float)$input;
            $out = (int)\round($total * $float);
            return true;
        }

        $error =
            "must be integer great than 1 (number of CELLs), a string `max` " .
            "to set <WIDTH>*<HEIGHT>, or decimal from 0 to 1 as fraction of max";
        return false;
    }

    /**
     * @param string $input
     * @param $out
     * @param $error
     * @param int|null $max
     * @return bool
     */
    private function validatePositiveInt(
        string $input,
        &$out,
        &$error,
        int $max = null
    ): bool {
        if (!\preg_match('/^[1-9]\\d*$/', $input)) {
            $error = 'must be positive integer';
            return false;
        }
        $int = (int)$input;
        if ((string)$int !== $input) {
            $error = 'is invalid or is too large number';
            return false;
        }
        if ($max !== null && $int > $max) {
            $error = "must be less then or equal to $max";
            return false;
        }

        $out = $int;
        return true;
    }
}
