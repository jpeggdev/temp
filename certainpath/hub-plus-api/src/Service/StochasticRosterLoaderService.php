<?php

namespace App\Service;

use App\Client\FileClient;
use App\DTO\StochasticRosterDTO;
use App\ValueObject\StochasticRoster;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

readonly class StochasticRosterLoaderService
{
    public function __construct(
        private FileClient $fileClient,
    ) {
    }

    /**
     * @return StochasticRosterDTO[]
     *
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function getRoster(): array
    {
        $roster = [];
        $files = $this->fileClient->list(
            'stochastic-files',
            'roster/'
        );
        $excelFiles = array_filter($files, static function ($file) {
            return 'xlsx' === pathinfo($file, PATHINFO_EXTENSION);
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
