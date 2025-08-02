<?php

namespace App\ValueObject;

readonly class FileHash
{
    private function __construct(
        private string $filePath,
    ) {
    }

    public static function fromFileSystem(string $filePath): self
    {
        return new self($filePath);
    }

    public function getString(): string
    {
        return md5_file($this->filePath);
    }
}
