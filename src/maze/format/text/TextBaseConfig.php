<?php

namespace VovanVE\MazeProject\maze\format\text;

class TextBaseConfig
{
    public $in = 'i';
    public $wall = '#';
    public $out = 'E';

    protected function configureBase(array $options): void
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'wall':
                    $this->validateOptionValueString($name, $value);
                    $this->wall = $value;
                    break;

                case 'in':
                    $this->validateOptionValueString($name, $value);
                    $this->in = $value;
                    break;

                case 'out':
                    $this->validateOptionValueString($name, $value);
                    $this->out = $value;
                    break;

                default:
                    throw new \InvalidArgumentException(
                        "Unknown option `$name`"
                    );
            }
        }
    }

    protected function repeatStringToLength(string $str, int $length): string
    {
        return \mb_substr(
            \str_repeat(
                $str,
                \ceil($length / \mb_strlen($str, 'UTF-8'))
            ),
            0,
            $length,
            'UTF-8'
        );
    }

    protected function validateOptionValueString(string $name, $value): void
    {
        if (!\is_string($value) || '' === $value) {
            throw new \InvalidArgumentException(
                "Value for `$name` must be non empty string"
            );
        }
    }
}
