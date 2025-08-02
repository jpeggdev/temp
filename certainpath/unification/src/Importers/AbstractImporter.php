<?php

namespace App\Importers;

use App\AbstractMigrator;
use App\Exceptions\PartialProcessingException;
use App\Repository\TradeRepository;
use App\ValueObjects\CompanyObject;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

use function App\Functions\app_getDecimal;

abstract class AbstractImporter extends AbstractMigrator
{
    private const SUPPORTED_OPTIONS = [
        'update-records',
    ];

    protected array $tradeNameIdMap = [];
    /** @var Exception[] */
    private array $exceptions = [];

    public function __construct(
        protected CompanyObject $companyObject,
        protected EntityManagerInterface $entityManager,
        protected TradeRepository $tradeRepository
    ) {
        parent::__construct(
            $companyObject,
            $entityManager
        );

        $this->supportedOptions = array_merge(
            $this->supportedOptions,
            self::SUPPORTED_OPTIONS,
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws PartialProcessingException
     */
    public function import(array $records = [ ]): self
    {
        $startTime = microtime(true);

        // Get Initial Record Count
        $this->startCount = $this->countRecords();

        // Set Counters
        $this->initialize($records);

        $chunks = array_chunk($records, 5000, true);
        $db = $this->entityManager->getConnection();
        //echo 'DATABASE IS: ' . $db->getDatabase() . PHP_EOL;
        $chunkCounter = 0;
        $totalChunks = count($chunks);
        foreach ($chunks as $_records) {
            $chunkCounter++;
            echo
                $this->companyObject->identifier
                 . ': '
                . date('Y-m-d H:i:s')
                . ' '
                . 'START: Processing Chunk '
                . $chunkCounter . ' of ' . $totalChunks . PHP_EOL;
            try {
                $db->beginTransaction();
                // Import Objects
                $this->records = $_records;
                $this->importRecords();
                if ($this->dryRun) {
                    $db->rollBack();
                } else {
                    $db->commit();
                }
            } catch (Exception $e) {
                $this->exceptions[] = $e;
                echo
                    $this->companyObject->identifier
                    . ': '
                    . date('Y-m-d H:i:s')
                    . ' '
                    . 'ERROR Processing Chunk: '
                    . $chunkCounter . ' of ' . $totalChunks
                    . ': '
                    . $e->getMessage()
                    . PHP_EOL;
                $db->rollBack();
            }
            echo
                $this->companyObject->identifier
                . ': '
                . date('Y-m-d H:i:s')
                . ' '
                . 'DONE: Processing Chunk '
                . $chunkCounter . ' of ' . $totalChunks . PHP_EOL;
        }

        // Get Final Record Count
        $this->endCount = $this->countRecords();

        $this->runTime = app_getDecimal((
            microtime(true) - $startTime
        ));

        if (!empty($this->exceptions)) {
            $partialProcessingException = new PartialProcessingException(
                'Errors occurred during import'
            );
            $partialProcessingException->setExceptions(
                $this->exceptions
            );
            $partialProcessingException->setImportResult(
                $this
            );
            throw $partialProcessingException;
        }

        return $this;
    }

    /**
     * Import records
     *
     * @return boolean
     */
    abstract protected function importRecords(): bool;

    protected function getTradeIdForName(): ?int
    {
        $tradeName = $this->getTrade();
        if (isset($this->tradeNameIdMap[$tradeName])) {
            return $this->tradeNameIdMap[$tradeName];
        }
        $tradeId = $this->tradeRepository->findByName(
            $tradeName
        )->getId();
        $this->tradeNameIdMap[$tradeName] = $tradeId;
        return $tradeId;
    }
}
