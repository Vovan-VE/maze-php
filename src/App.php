<?php

namespace VovanVE\MazeProject;

use VovanVE\MazeProject\cli\Console;
use VovanVE\MazeProject\cli\getopt\InvalidOptionException;
use VovanVE\MazeProject\cli\getopt\OptionsParser;
use VovanVE\MazeProject\commands\BaseCommand;
use VovanVE\MazeProject\commands\CommandInterface;
use VovanVE\MazeProject\commands\GenerateCommand;
use VovanVE\MazeProject\commands\HelpCommand;
use VovanVE\MazeProject\commands\UnknownCommand;

class App
{
    /** @var string */
    private $bin;
    /** @var CommandInterface */
    private $command;
    /** @var string[] */
    private $args;

    private const COMMANDS = [
        'gen' => GenerateCommand::class,
        'help' => HelpCommand::class,
    ];
    private const DEFAULT_COMMAND = 'gen';

    public function __construct(array $argv)
    {
        [$this->bin] = $argv;
        [$this->command, $this->args] = $this->resolveCommand(
            array_slice($argv, 1)
        );
    }

    public function run(): int
    {
        try {
            return $this->command->run($this->args);
        } catch (InvalidOptionException $e) {
            Console::stderr('maze: ', $e->getMessage(), PHP_EOL);
            Console::stderr("Run `maze help {$this->command->getName()}`", PHP_EOL);
            return 2;
        }
    }

    public function getCommand(string $name): ?CommandInterface
    {
        $class = self::COMMANDS[$name] ?? null;
        if (null === $class) {
            return null;
        }

        /** @var BaseCommand|string $class */
        return $class::create($this, $name);
    }

    /**
     * Get all commands
     *
     * ```
     * [
     *     'name' => [CommandInterface $command, bool $isDefault],
     *     ...
     * ]
     * ```
     * @return array
     */
    public function getAllCommands(): array
    {
        $result = [];
        foreach (self::COMMANDS as $name => $class) {
            /** @var BaseCommand|string $class */
            $result[$name] = [
                $class::create($this, $name),
                $name === self::DEFAULT_COMMAND
            ];
        }
        ksort($result);
        return $result;
    }

    protected function resolveCommand(array $args): array
    {
        if ($args) {
            [$commandName] = $args;
            $command = $this->getCommand($commandName);
            if (null !== $command) {
                return [$command, array_slice($args, 1)];
            }

            // maze ''
            // maze unknown-command
            if ('' === $commandName || '-' !== $commandName[0]) {
                return [new UnknownCommand($this, $commandName), []];
            }

            // edge case: -h[value] or --help
            $opts = (new OptionsParser('h::', ['help']))
                ->setBypassUnknown(true)
                ->parse($args);
            if ($opts->hasOpt('h', 'help')) {
                return [
                    $this->getCommand('help'),
                    \array_merge(
                        $opts->hasOpt('h')
                            ? ['-h' . $opts->getOpt('h')]
                            : [],
                        $opts->getMixedValues(),
                        ['--'],
                        $opts->getRestValues()
                    ),
                ];
            }
        }

        return [$this->getCommand(self::DEFAULT_COMMAND), $args];
    }
}
