<?php

namespace App\Services;

use App\Client\FileClient;
use App\ValueObjects\LifeFileCollection;

class LifeFileService
{
    public function __construct(
        private readonly FileClient $fileClient
    ) {
    }

    public function getLifeFileCollection(
        string $lifeFolderName
    ): LifeFileCollection {
        $files = $this->fileClient->list(
            'stochastic-files',
            'sync/customer-data/'
            . $lifeFolderName
            . '/4 Power Data/'
        );
        if (empty($files)) {
            $files = $this->fileClient->list(
                'stochastic-files',
                'sync/customer-data/'
                . $lifeFolderName
                . '/4 Power Data Files/'
            );
            return LifeFileCollection::fromCloudFiles(
                $files,
                true
            );
        }

        return LifeFileCollection::fromCloudFiles(
            $files
        );
    }
}
