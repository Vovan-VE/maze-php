<?php

namespace VovanVE\MazeProject\commands;

use VovanVE\MazeProject\App;

abstract class BaseCommand implements CommandInterface
{
    /** @var App */
    protected $app;
    /** @var string */
    protected $name;

    public function __construct(App $app, string $name)
    {
        $this->app = $app;
        $this->name = $name;
    }

    public static function create(App $app, string $name): self
    {
        return new static($app, $name);
    }
}
