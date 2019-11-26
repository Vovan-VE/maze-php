<?php

namespace VovanVE\MazeProject\commands;

interface CommandInterface
{
    public function run(array $args): int;

    public function getUsageHelp(): string;
}
