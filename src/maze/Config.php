<?php

namespace VovanVE\MazeProject\maze;

class Config
{
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var int */
    private $branchLength;
    /** @var string */
    private $format;

    public function __construct(
        int $width,
        int $height,
        int $branchLength,
        string $format
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->branchLength = $branchLength;
        $this->format = $format;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getBranchLength(): int
    {
        return $this->branchLength;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
