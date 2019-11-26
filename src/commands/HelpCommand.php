<?php

namespace VovanVE\MazeProject\commands;

class HelpCommand extends BaseCommand implements CommandInterface
{
    public function run(array $args): int
    {
        if (!$args) {
            echo $this->getUsageHelp();
            return 0;
        }

        [$commandName] = $args;
        $command = $this->app->getCommand($commandName);
        if (!$command) {
            echo "E! Unrecognized help topic: `$commandName`", PHP_EOL;
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
