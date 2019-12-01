<?php

namespace VovanVE\MazeProject\commands;

interface CommandInterface
{
    public function run(array $args): int;

    public function getName(): string;

    public function getUsageHelp(): string;
}
