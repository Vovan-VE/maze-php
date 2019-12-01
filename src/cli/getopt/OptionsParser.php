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

    private $short = [];
    private $long = [];

    private const BAD_SHORT_KEYS = '-:';

    /**
     * OptionsParser constructor.
     * @param string $short Short options in std `getopt()` manner
     * @param array $long Long options in std `getopt()` manner
     * @throws \InvalidArgumentException
     */
    public function __construct(string $short, array $long = [])
    {
        $this->short = $this->parseDefinition(
            self::expandShortDefinition($short)
        );
        $this->long = $this->parseDefinition($long);

        $overlapped = array_intersect_key($this->short, $this->long);
        if ($overlapped) {
            throw new \InvalidArgumentException(
                'Some options duplicated in short and long forms: '
                . join(', ', array_map('json_encode', array_keys($overlapped)))
            );
        }
    }

    /**
     * @param array $args
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

                if (!isset($this->long[$name])) {
                    throw new \InvalidArgumentException(
                        "unrecognized option: `--$name`"
                    );
                }

                $type = $this->long[$name];

                if ($type === self::V_NO) {
                    if ($value !== true) {
                        throw new \InvalidArgumentException(
                            "option `--$name` cannot be used with value"
                        );
                    }
                } elseif ($type === self::V_REQUIRED) {
                    if ($value === true) {
                        // --a
                        if ($i === $count) {
                            throw new \InvalidArgumentException(
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
                if (!isset($this->short[$name])) {
                    throw new \InvalidArgumentException(
                        "unrecognized key: `-$name`"
                    );
                }

                $type = $this->short[$name];

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
                            throw new \InvalidArgumentException(
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

            $result[$name] = $value;
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

        return new Options($result,$plainValues,$rest);
    }

    /**
     * @param iterable|string[] $options
     * @return array
     */
    protected function parseDefinition(iterable $options): array
    {
        $result = [];

        foreach ($options as $option) {
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

            if (isset($result[$option])) {
                throw new \InvalidArgumentException(
                    "Duplicate option '$option'"
                );
            }

            $result[$option] = $type;
        }

        return $result;
    }

    /**
     * @param string $options
     * @return \Generator|string[]
     */
    public static function expandShortDefinition(string $options): \Generator
    {
        for ($i = 0, $len = \strlen($options); $i < $len; $i++) {
            $option = $options[$i];
            if (false !== \strpos(self::BAD_SHORT_KEYS, $option)) {
                throw new \InvalidArgumentException(
                    "Bad key `$option` at offset $i"
                );
            }
            for (
                $n = 1, $p = $i + 1;
                $n <= 2 && $p < $len && $options[$p] === ':';
                $n++, $p++
            ) {
                $option .= ':';
                $i++;
            }
            yield $option;
        }
    }

    /**
     * @return array
     */
    final public function getShort(): array
    {
        return $this->short;
    }

    /**
     * @return array
     */
    final public function getLong(): array
    {
        return $this->long;
    }
}
