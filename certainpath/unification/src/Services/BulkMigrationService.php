<?php

namespace App\Services;

use App\Client\FileClient;
use App\DTO\Domain\StochasticRosterDTO;
use App\Entity\Trade;
use App\Exceptions\FileCouldNotBeRetrieved;
use App\Exceptions\StochasticFilePathWasInvalid;
use App\Message\MigrationMessage;
use App\Parsers\MailManager\MailManagerParser;
use App\Parsers\MailManagerLife\MailManagerLifeParser;
use App\ValueObjects\StochasticFile;
use App\ValueObjects\TempFile;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class BulkMigrationService
{
    public function __construct(
        private StochasticRosterLoaderService $loaderService,
        private FileClient $fileClient,
        private MessageBusInterface $messageBus,
        private MigrationService $migrationService,
        private LifeFileService $lifeFileService
    ) {
    }

    /**
     * @param int $companyCountLimit
     * @param array|null $companyIdentifiers
     * @param int|null $recordLimit
     * @return MigrationMessage[]
     * @throws FileCouldNotBeRetrieved
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function bulkImport(
        int $companyCountLimit = 400,
        ?array $companyIdentifiers = null,
        ?int $recordLimit = null
    ): array {
        $imports = [];
        $companies = $this->loaderService->getRoster();
        $fileNameCache = [];
        foreach ($companies as $company) {
            $downloadedLifeFiles = [];
            echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                .
                'Loading: ' . $company->intacctId . PHP_EOL;
            if ($companyIdentifiers && !in_array($company->intacctId, $companyIdentifiers, true)) {
                echo 'Not Included: ' . $company->intacctId . PHP_EOL;
                continue;
            }
            try {
                echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                . 'START: Process Life Files'
                . PHP_EOL
                ;
                $this->processLifeFiles(
                    $company,
                    $downloadedLifeFiles,
                    $fileNameCache
                );
                echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                . 'END: Process Life Files '
                . PHP_EOL
                ;
                $file = StochasticFile::fromMasterSpreadsheet($company->fileName);
                if (isset($fileNameCache[$file->getFileName()])) {
                    echo 'Skipping duplicate file: ' . $file->getFileName() . PHP_EOL;
                    continue;
                }
                $fileNameCache[$file->getFileName()] = true;
                $downloadedFilePath = $this->fileClient->download(
                    'stochastic-files',
                    'sync/lists/' . $file->getFileName()
                );
            } catch (StochasticFilePathWasInvalid | FileCouldNotBeRetrieved $e) {
                echo $company->intacctId . ': ' . $e->getMessage() . PHP_EOL;
                continue;
            }
            echo
            $company->intacctId
            . ': '
            . date('Y-m-d H:i:s')
            . ': '
            . 'START: Dispatch Mail Manager '
            . PHP_EOL;
            $migrationMessage = $this->dispatchFileAndGetMessagePayload(
                $downloadedFilePath,
                $recordLimit,
                $company,
                MailManagerParser::getSourceName()
            );
            echo
            $company->intacctId
            . ': '
            . date('Y-m-d H:i:s')
            . ': '
            . 'END: Dispatch Mail Manager '
            . PHP_EOL
            ;
            if ($migrationMessage) {
                $imports[] = $migrationMessage;
            }
            foreach ($downloadedLifeFiles as $downloadedLifeFile) {
                echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                . 'START: Dispatch Life: '
                . $downloadedLifeFile['file']
                . PHP_EOL
                ;
                $migrationMessage = $this->dispatchFileAndGetMessagePayload(
                    $downloadedLifeFile['file'],
                    $recordLimit,
                    $company,
                    MailManagerLifeParser::getSourceName(),
                    $downloadedLifeFile['trade']
                );
                echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                . 'END: Dispatch Life: '
                . $downloadedLifeFile['file']
                . PHP_EOL
                ;
                if ($migrationMessage) {
                    $imports[] = $migrationMessage;
                }
            }
            if (count($imports) >= $companyCountLimit) {
                break;
            }
        }
        return $imports;
    }

    /**
     * @throws FileCouldNotBeRetrieved
     * @throws StochasticFilePathWasInvalid
     */
    private function processLifeFiles(
        StochasticRosterDTO $company,
        array &$downloadedFiles,
        array &$fileNameCache
    ): void {
        if ($company->lifeFolderName) {
            $lifeFileCollection = $this->lifeFileService->getLifeFileCollection(
                $company->lifeFolderName
            );
            $company->hvacLifeFileName = null;
            $company->plumbingLifeFileName = null;
            $company->electricalLifeFileName = null;
            $company->roofingLifeFileName = null;

            foreach ($lifeFileCollection->getLifeFiles() as $lifeFile) {
                if ($lifeFile->trade->isHvac()) {
                    $company->hvacLifeFileName = $lifeFile->fileName;
                }
                if ($lifeFile->trade->isPlumbing()) {
                    $company->plumbingLifeFileName = $lifeFile->fileName;
                }
                if ($lifeFile->trade->isElectrical()) {
                    $company->electricalLifeFileName = $lifeFile->fileName;
                }
                if ($lifeFile->trade->isRoofing()) {
                    $company->roofingLifeFileName = $lifeFile->fileName;
                }
            }
            $powerDataFolderName = '/4 Power Data/';
            if ($lifeFileCollection->isAlternatePath()) {
                $powerDataFolderName = '/4 Power Data Files/';
            }
            if ($company->electricalLifeFileName) {
                $electricalLifeFile = StochasticFile::fromMasterSpreadsheetLife(
                    $company->electricalLifeFileName,
                    $company->lifeFolderName
                );
                if (isset($fileNameCache[$electricalLifeFile->getFileName()])) {
                    echo 'Skipping duplicate file: ' . $electricalLifeFile->getFileName() . PHP_EOL;
                } else {
                    $downloadedElectricalFile = $this->fileClient->download(
                        'stochastic-files',
                        'sync/customer-data/'
                        . $company->lifeFolderName
                        . $powerDataFolderName
                        . $electricalLifeFile->getFileName()
                    );
                    $downloadedFiles[] = [
                        'file' => $downloadedElectricalFile,
                        'trade' => Trade::electrical()
                    ];
                }
            }
            if ($company->plumbingLifeFileName) {
                $plumbingLifeFile = StochasticFile::fromMasterSpreadsheetLife(
                    $company->plumbingLifeFileName,
                    $company->lifeFolderName
                );
                if (isset($fileNameCache[$plumbingLifeFile->getFileName()])) {
                    echo 'Skipping duplicate file: ' . $plumbingLifeFile->getFileName() . PHP_EOL;
                } else {
                    $downloadedPlumbingFile = $this->fileClient->download(
                        'stochastic-files',
                        'sync/customer-data/'
                        . $company->lifeFolderName
                        . $powerDataFolderName
                        . $plumbingLifeFile->getFileName()
                    );
                    $downloadedFiles[] = [
                        'file' => $downloadedPlumbingFile,
                        'trade' => Trade::plumbing()
                    ];
                }
            }
            if ($company->hvacLifeFileName) {
                $hvacLifeFile = StochasticFile::fromMasterSpreadsheetLife(
                    $company->hvacLifeFileName,
                    $company->lifeFolderName
                );
                if (isset($fileNameCache[$hvacLifeFile->getFileName()])) {
                    echo 'Skipping duplicate file: ' . $hvacLifeFile->getFileName() . PHP_EOL;
                } else {
                    $downloadedHvacFile = $this->fileClient->download(
                        'stochastic-files',
                        'sync/customer-data/'
                        . $company->lifeFolderName
                        . $powerDataFolderName
                        . $hvacLifeFile->getFileName()
                    );
                    $downloadedFiles[] = [
                        'file' => $downloadedHvacFile,
                        'trade' => Trade::hvac()
                    ];
                }
            }
        }
    }

    private function dispatchFileAndGetMessagePayload(
        string $fileToProcess,
        ?int $recordLimit,
        StochasticRosterDTO $company,
        string $dataSource,
        Trade $trade = null
    ): ?MigrationMessage {
        $migrationMessage = null;
        if (
            $this->migrationService->isFileValid(
                $fileToProcess,
                $recordLimit
            )
        ) {
            $migrationMessage = new MigrationMessage(
                $company->intacctId,
                $fileToProcess,
                $dataSource,
                null,
                $trade?->getName(),
                [],
                $recordLimit
            );
            echo
                $company->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                .
                'Dispatching: ' . $company->intacctId . PHP_EOL;
            $this->messageBus->dispatch(
                $migrationMessage
            );
        } else {
            $file = TempFile::fromFullPath(
                $fileToProcess
            );

            echo 'File is Corrupted: '
                . $company->intacctId . ': '
                . $file->getRelativePath()
                . PHP_EOL;
        }
        return $migrationMessage;
    }
}
