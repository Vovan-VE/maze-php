<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\cli\Console;

class GenerateCommand extends BaseCommand
{
    public function run(array $args): int
    {
        Console::stderr('I: not implemented', PHP_EOL);
        return 0;
    }

    public function getUsageHelp(): string
    {
        return <<<'_END'
maze [gen] [options]

Generate a maze and export it to stdout.

Since `gen` in the default command, the command name `gen` is optional.

_END;
    }
}
