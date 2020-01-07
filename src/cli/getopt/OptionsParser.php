<?php

namespace VovanVE\MazeProject\cli\getopt;

/**
 * CLI arguments parser like std `getopt`
 */
class OptionsParser
{
    public const V_NO = 1;
    public const V_REQUIRED = 2;
    public const V_OPTIONAL = 3;

    private $types = [];
    private $shortAlias = [];
    private $longAlias = [];
    /** @var \Closure[] */
    private $handlers = [];

    /** @var bool Whether to bypass unknown options as plain values */
    private $bypassUnknown = false;

    /**
     * OptionsParser constructor.
     * @param array $long
     * @throws \InvalidArgumentException
     */
    public function __construct(array $long = [])
    {
        $this->initDefinition($long);
    }

    /**
     * @param string[] $args
     * @return Options
     */
    public function parse(array $args): Options
    {
        $result = [];
        $plainValues = [];
        for ($i = 0, $count = \count($args); $i < $count;) {
            $arg = $args[$i];
            $len = \strlen($arg);
            if ($len <= 1 || '-' !== $arg[0]) {
                // ''
                // -
                // a
                // a...
                $plainValues[] = $arg;
                $i++;
                continue;
            }

            if ($arg[1] === '-') {
                if ($len === 2) {
                    // --
                    $i++;
                    break;
                }

                // --...
                $i++;
                $eqPos = \strpos($arg, '=', 2);
                if (false === $eqPos) {
                    // --name
                    $name = \substr($arg, 2);
                    $value = true;
                } else {
                    // --name=...
                    $name = \substr($arg, 2, $eqPos - 2);
                    $value = \substr($arg, $eqPos + 1);
                }

                if (!isset($this->longAlias[$name])) {
                    if ($this->getBypassUnknown()) {
                        $plainValues[] = $arg;
                        continue;
                    }

                    throw new InvalidOptionException(
                        "unrecognized option: `--$name`"
                    );
                }

                $mainName = $this->longAlias[$name];
                $type = $this->types[$mainName];

                if ($type === self::V_NO) {
                    if ($value !== true) {
                        throw new InvalidOptionException(
                            "option `--$name` cannot be used with value"
                        );
                    }
                } elseif ($type === self::V_REQUIRED) {
                    if ($value === true) {
                        // --a
                        if ($i === $count) {
                            throw new InvalidOptionException(
                                "option `--$name` must be used with value"
                            );
                        }
                        // consume next arg
                        $value = $args[$i];
                        $i++;
                    }
                }
            } else {
                // -a
                // -a...
                $name = $arg[1];
                if (!isset($this->shortAlias[$name])) {
                    if ($this->getBypassUnknown()) {
                        $plainValues[] = $arg;
                        $i++;
                        continue;
                    }

                    throw new InvalidOptionException(
                        "unrecognized key: `-$name`"
                    );
                }

                $mainName = $this->shortAlias[$name];
                $type = $this->types[$mainName];

                if ($len === 2) {
                    // '-a'
                    // =>
                    // ''
                    $restStr = '';
                } else {
                    // '-arest'
                    // =>
                    // 'rest'
                    $restStr = \substr($arg, 2);
                }

                if ($type === self::V_NO) {
                    $value = true;
                    if ('' === $restStr) {
                        $i++;
                    } else {
                        $args[$i] = "-$restStr";
                    }
                } elseif ($type === self::V_REQUIRED) {
                    $i++;
                    if ($restStr !== '') {
                        // -a 'rest'
                        $value = $restStr;
                    } else {
                        // -a
                        if ($i === $count) {
                            throw new InvalidOptionException(
                                "key `-$name` must be used with value"
                            );
                        }
                        // consume next arg
                        $value = $args[$i];
                        $i++;
                    }
                } else {
                    $i++;
                    // -a ''
                    // -a 'rest'
                    $value = $restStr;
                }
            }

            if (isset($this->handlers[$mainName])) {
                $value = ($this->handlers[$mainName])(
                    $result[$mainName] ?? null,
                    $value
                );
            }
            $result[$mainName] = $value;
        }

        if ($i >= $count) {
            $rest = [];
        } else {
            if ($i > 0) {
                $rest = \array_slice($args, $i);
            } else {
                $rest = $args;
            }
        }

        return new Options($result, $plainValues, $rest);
    }

    /**
     * @param iterable|string[]|\Closure[] $options
     * @return void
     */
    protected function initDefinition(iterable $options): void
    {
        $allAliases = [];
        $types = [];
        $short = [];
        $long = [];
        $handlers = [];

        foreach ($options as $option => $handler) {
            if (is_int($option)) {
                $option = $handler;
                $handler = null;
            } elseif (!$handler instanceof \Closure) {
                throw new \InvalidArgumentException(
                    'Value in `key=>value` case must be a Closure'
                );
            }

            $type = self::V_NO;
            if (\strlen($option) > 1 && $option[-1] === ':') {
                $type = self::V_REQUIRED;
                if (\strlen($option) > 2 && $option[-2] === ':') {
                    $type = self::V_OPTIONAL;
                    $option = \substr($option, 0, -2);
                } else {
                    $option = \substr($option, 0, -1);
                }
            }

            $aliases = array_unique(explode('|', $option));
            [$name] = $aliases;
            $types[$name] = $type;
            if (null !== $handler) {
                $handlers[$name] = $handler;
            }

            foreach ($aliases as $alias) {
                if ('' === $alias || '-' === $alias[0]) {
                    throw new \InvalidArgumentException(
                        "Bad option name '$alias'"
                    );
                }

                if (isset($allAliases[$alias])) {
                    throw new \InvalidArgumentException(
                        "Duplicate option '$alias'"
                    );
                }

                $allAliases[$alias] = true;

                if (\strlen($alias) === 1) {
                    $short[$alias] = $name;
                } else {
                    $long[$alias] = $name;
                }
            }
        }

        $this->types = $types;
        $this->shortAlias = $short;
        $this->longAlias = $long;
        $this->handlers = $handlers;
    }

    /**
     * @return array
     */
    final public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array
     */
    final public function getShortAlias(): array
    {
        return $this->shortAlias;
    }

    /**
     * @return array
     */
    final public function getLongAlias(): array
    {
        return $this->longAlias;
    }

    /**
     * Whether to bypass unknown options as plain values
     * @return bool
     */
    final public function getBypassUnknown(): bool
    {
        return $this->bypassUnknown;
    }

    /**
     * Whether to bypass unknown options as plain values
     * @param bool $bypassUnknown
     * @return $this
     */
    public function setBypassUnknown(bool $bypassUnknown): self
    {
        $this->bypassUnknown = $bypassUnknown;
        return $this;
    }
}
