<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\cli\Console;
use VovanVE\MazeProject\cli\getopt\OptionsParser;

class HelpCommand extends BaseCommand
{
    public function run(array $args): int
    {
        $opts = (new OptionsParser('h::', ['help']))->parse($args);

        if ($opts->hasOpt('h') && '' !== $opts->getOpt('h')) {
            Console::stderr(
                'W! key `-h` used with a value - did you mean ',
                '`-H` (`--height`) from `gen` command?',
                PHP_EOL
            );
        }

        $values = $opts->getMixedValues();
        if (!$values) {
            echo $this->getUsageHelp();
            return 0;
        }

        [$commandName] = $values;
        $command = $this->app->getCommand($commandName);
        if (!$command) {
            Console::stderr("E! Unknown command `$commandName`", PHP_EOL);
            return 1;
        }

        echo $command->getUsageHelp();
        return 0;
    }

    public function getUsageHelp(): string
    {
        return <<<'_END'
maze help [command]

Show help either about specified `command` or common usage help.

_END;
    }
}
