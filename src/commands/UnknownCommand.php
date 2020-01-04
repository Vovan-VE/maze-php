<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\cli\Console;

class UnknownCommand extends BaseCommand
{
    public function run(array $args): int
    {
        Console::stderr("E! Unknown command `{$this->name}`", \PHP_EOL);
        return 1;
    }

    public function getUsageHelp(): string
    {
        throw new \LogicException('This is a stub for unknown command');
    }
}
