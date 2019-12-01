<?php

namespace VovanVE\MazeProject\cli\getopt;

/**
 * Getter utility for passed options values
 */
class Options
{
    /** @var array Map of passed keys and switches with corresponding values */
    private $opt;
    /** @var string[] List of mixed plain values before `--` */
    private $mixed;
    /** @var string[] List of rest values after `--` */
    private $rest;

    /**
     * Options constructor.
     * @param array $opt Options in key=>value form
     * @param array $mixed Mixed plain values before `--`
     * @param array $rest Rest values after `--`
     */
    public function __construct(
        array $opt = [],
        array $mixed = [],
        array $rest = []
    ) {
        $this->opt = $opt;
        $this->mixed = $mixed;
        $this->rest = $rest;
    }

    /**
     * Whether an option was set
     * @param string $name
     * @return bool
     */
    public function hasOpt(string $name): bool
    {
        return isset($this->opt[$name]) || array_key_exists($name, $this->opt);
    }

    /**
     * Get option value
     * @param string $name
     * @param mixed $default
     * @return string|mixed|null Return option value if it was set, or default
     * value otherwise
     */
    public function getOpt(string $name, $default = null)
    {
        return $this->hasOpt($name) ? $this->opt[$name] : $default;
    }

    /**
     * Get all passed options map
     * @return array
     */
    final public function getOptions(): array
    {
        return $this->opt;
    }

    /**
     * Get mixed plain values passed before `--`
     * @return string[]
     */
    final public function getMixedValues(): array
    {
        return $this->mixed;
    }

    /**
     * Get rest values passed after `--`
     * @return string[]
     */
    final public function getRestValues(): array
    {
        return $this->rest;
    }
}
