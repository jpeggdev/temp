<?php

namespace App\Exporters;

use App\AbstractMigrator;
use App\Client\FileClient;
use App\ValueObjects\CompanyObject;
use Doctrine\ORM\EntityManagerInterface;

use function App\Functions\app_getDecimal;

abstract class AbstractExporter extends AbstractMigrator
{
    protected const SUPPORTED_OPTIONS = [
    ];

    public function __construct(
        CompanyObject $companyObject,
        protected EntityManagerInterface $entityManager,
        protected FileClient $fileClient
    ) {
        parent::__construct(
            $companyObject,
            $this->entityManager
        );

        $this->supportedOptions = array_merge(
            $this->supportedOptions,
            self::SUPPORTED_OPTIONS,
        );
    }

    public function export(array $records = [ ]): self
    {
        $startTime = microtime(true);

        $this->initialize($records);
        $this->outputString = $this->exportRecords($records);

        $this->runTime = app_getDecimal((
            microtime(true) - $startTime
        ));

        return $this;
    }

    abstract protected function exportRecords(array $data): ?string;

    abstract protected function getExportType(): string;

    abstract protected function getHeader(): array;
}
