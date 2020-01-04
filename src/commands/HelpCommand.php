<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\cli\Console;
use VovanVE\MazeProject\cli\getopt\OptionsParser;

class HelpCommand extends BaseCommand
{
    public function run(array $args): int
    {
        $opts = (new OptionsParser(['h::', 'help', 'l|list']))
            ->setBypassUnknown(true)
            ->parse($args);

        if ($opts->hasOpt('h') && '' !== $opts->getOpt('h')) {
            Console::stderr(
                'W! key `-h` used with a value - did you mean ',
                '`-H` (`--height`) from `gen` command?',
                \PHP_EOL
            );
        }

        if ($opts->hasOpt('l')) {
            echo <<<'_END'
maze [command] [options]

Available commands:

_END;

            foreach (
                $this->app->getAllCommands()
                as $name => [$command, $isDefault]
            ) {
                echo '  ', $name;
                if ($isDefault) {
                    echo '       default command';
                }
                echo \PHP_EOL;
            }

            echo <<<'_END'

Run `maze help [command]` to see help about specific command.

_END;
            return 0;
        }

        $values = $opts->getMixedValues();
        if ($values) {
            [$commandName] = $values;
            $command = $this->app->getCommand($commandName);
            if ($command) {
                echo $command->getUsageHelp();
                return 0;
            }
            Console::stderr(
                "E! Unknown help topic `$commandName`",
                \PHP_EOL
            );
        }

        $this->showAppUsage();

        return 0;
    }

    public function getUsageHelp(): string
    {
        return <<<'_END'
maze help [options] [command]
maze (-h | --help) [options] [command]

Show help either about specified `command` or common usage help.

Options:

    -l, --list
        List all available commands.

_END;
    }

    protected function showAppUsage(): void
    {
        echo <<<'_END'
maze [command] [options]

Run `maze help -l` to see available commands.

Run `maze help [command]` to see help about specific command.

The default command in `gen`. Run `maze help gen` too see help for generation.

TBW: ...

_END;
    }
}
