<?php

namespace VovanVE\MazeProject;

use VovanVE\MazeProject\commands\BaseCommand;
use VovanVE\MazeProject\commands\CommandInterface;
use VovanVE\MazeProject\commands\HelpCommand;

class App
{
    /** @var string */
    private $bin;
    /** @var CommandInterface|null */
    private $command;
    /** @var string[] */
    private $args;

    private const COMMANDS = [
        'help' => HelpCommand::class,
    ];

    public function __construct(array $argv)
    {
        [$this->bin] = $argv;
        [$this->command, $this->args] = $this->resolveCommand(array_slice($argv, 1));
    }

    public function run(): int
    {
        echo 'Not implemented yet', PHP_EOL;
        return 0;
    }

    public function getCommand(string $name, array $args = []): ?CommandInterface
    {
        $class = self::COMMANDS[$name] ?? null;
        if (null === $class) {
            return null;
        }
        /** @var BaseCommand|string $class */
        return new $class($this, $args);
    }

    protected function resolveCommand(array $args): array
    {
        if ($args) {
            [$commandName] = $args;
            $command = $this->getCommand($commandName);
            if (null !== $command) {
                return [$command, array_slice($args, 1)];
            }

            // edge case
            $opts = getopt('h:', ['help']);
        }

    }
}
