<?php

namespace App\ValueObjects;

use App\Entity\Trade;

class LifeFileCollection
{
    /** @var LifeFile[] */
    private array $lifeFiles = [];
    private bool $isAlternatePath = false;

    public function __construct(array $files, bool $isAlternatePath = false)
    {
        $this->isAlternatePath = $isAlternatePath;
        foreach ($files as $file) {
            //if file does not end with .DBF, skip it
            if (!str_ends_with($file, '.DBF')) {
                continue;
            }
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            if (str_contains($fileName, Trade::ELECTRICAL_CODE)) {
                $this->addFile($fileName, Trade::electrical());
            }
            if (str_contains($fileName, Trade::HVAC_CODE)) {
                $this->addFile($fileName, Trade::hvac());
            }
            if (str_contains($fileName, Trade::PLUMBING_CODE)) {
                $this->addFile($fileName, Trade::plumbing());
            }
            if (str_contains($fileName, Trade::ROOFING_CODE)) {
                $this->addFile($fileName, Trade::roofing());
            }
        }
    }

    public static function fromCloudFiles(
        array $files,
        bool $isAlternatePath = false
    ): self {
        return new self($files, $isAlternatePath);
    }

    private function addFile(
        string $fileName,
        Trade $trade
    ): void {
        $this->lifeFiles[] = new LifeFile($fileName, $trade);
    }

    /**
     * @return LifeFile[]
     */
    public function getLifeFiles(): array
    {
        return $this->lifeFiles;
    }

    public function isAlternatePath(): bool
    {
        return $this->isAlternatePath;
    }
}
