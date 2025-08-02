<?php

namespace App\ValueObjects;

readonly class TempFile
{
    public function __construct(
        private string $fullTempFilePath
    ) {
    }

    public static function fromFullPath(string $fullTempFilePath): self
    {
        return new self($fullTempFilePath);
    }

    public function getRelativePath(): string
    {
        return substr(
            $this->fullTempFilePath,
            strpos(
                $this->fullTempFilePath,
                '/tmp/'
            ) + 5
        );
    }

    public function getFullPath(): string
    {
        return $this->fullTempFilePath;
    }
}
