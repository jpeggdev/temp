<?php

namespace App\Services;

use App\Client\FileClient;
use App\DTO\Domain\StochasticRosterDTO;
use App\Exceptions\FileCouldNotBeRetrieved;
use App\ValueObjects\StochasticRoster;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

readonly class StochasticRosterLoaderService
{
    public function __construct(
        private FileClient $fileClient
    ) {
    }

    /**
     * @return StochasticRosterDTO[]
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws FileCouldNotBeRetrieved
     */
    public function getRoster(): array
    {
        $roster = [];
        $files = $this->fileClient->list(
            'stochastic-files',
            'roster/'
        );
        $excelFiles = array_filter($files, static function ($file) {
            $path = pathinfo($file, PATHINFO_EXTENSION);
            return
                $path === 'xlsx' &&
                !str_contains($file, 'backup');
        });
        foreach ($excelFiles as $excelFile) {
            $downloadedFile = $this->fileClient->download(
                'stochastic-files',
                $excelFile
            );
            $partialRoster = StochasticRoster::fromExcelFile(
                $downloadedFile
            );
            foreach ($partialRoster->getRoster() as $company) {
                $roster[] = $company;
            }
        }
        return $roster;
    }
}
