<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\App;

class BaseCommand
{
    /** @var App */
    protected $app;
    /** @var string */
    protected $name;
    /** @var string[] */
    protected $args;

    public function __construct(App $app, string $name, array $args = [])
    {
        $this->app = $app;
        $this->name = $name;
        $this->args = $args;
    }
}
