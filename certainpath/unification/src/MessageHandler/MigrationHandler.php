<?php

namespace App\MessageHandler;

use App\Exceptions\FileConverterException;
use App\Exceptions\PartialProcessingException;
use App\Message\MigrationMessage;
use App\Parsers\MailManagerLife\MailManagerLifeParser;
use App\Parsers\ServiceTitan\ServiceTitanParser;
use App\Repository\CompanyRepository;
use App\Services\CustomerMetricsService;
use App\Services\MigrationService;
use League\Csv\Exception;
use ReflectionException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

#[AsMessageHandler]
class MigrationHandler
{
    private array $partialExceptions = [];
    public function __construct(
        private readonly MigrationService $migrationService,
        private readonly CompanyRepository $companyRepository,
        private readonly CustomerMetricsService $customerMetricsService
    ) {
    }

    /**
     * @param MigrationMessage $message
     */
    public function __invoke(MigrationMessage $message): void
    {
        try {
            $this->processProspectRecords($message);
        } catch (\Exception $exception) {
            $this->processError('Prospects', $message, $exception);
        }
        try {
            $this->processCustomerRecords($message);
        } catch (\Exception $exception) {
            $this->processError('Customers', $message, $exception);
        }
        try {
            $this->processAddressRecords($message);
        } catch (\Exception $exception) {
            $this->processError('Addresses', $message, $exception);
        }
        try {
            $this->processInvoiceRecords($message);
        } catch (\Exception $exception) {
            $this->processError('Invoices', $message, $exception);
        }

        if (count($this->partialExceptions) > 0) {
            echo 'Job Finished with Partial Processing Exceptions and will get Re-Scheduled: '
                . PHP_EOL;
            $retryMessage = '';
            foreach ($this->partialExceptions as $exception) {
                $retryMessage .= $exception->getMessage() . PHP_EOL;
            }
            echo $retryMessage . PHP_EOL;
            throw new RecoverableMessageHandlingException(
                'Job Finished with Partial Processing Exceptions'
                . $retryMessage
            );
        }
    }

    /**
     * @param MigrationMessage $message
     * @return void
     * @throws Exception
     * @throws FileConverterException
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function processProspectRecords(MigrationMessage $message): void
    {
        $type = 'Prospects';
        $dataType = 'prospects';
        $this->processRecords($type, $dataType, $message);
    }

    /**
     * @param MigrationMessage $message
     * @return void
     * @throws Exception
     * @throws FileConverterException
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function processCustomerRecords(MigrationMessage $message): void
    {
        $type = 'Customers';
        $dataType = 'customers';
        $this->processRecords($type, $dataType, $message);
    }

    /**
     * @param MigrationMessage $message
     * @return void
     * @throws Exception
     * @throws FileConverterException
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function processAddressRecords(MigrationMessage $message): void
    {
        $type = 'Addresses';
        $dataType = 'addresses';
        $this->processRecords($type, $dataType, $message);
    }

    /**
     * @param MigrationMessage $message
     * @return void
     * @throws Exception
     * @throws FileConverterException
     * @throws ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    private function processInvoiceRecords(MigrationMessage $message): void
    {
        $type = 'Invoices';
        $dataType = 'invoices';
        $this->processRecords($type, $dataType, $message);

        if (
            in_array($message->dataSource, [
                MailManagerLifeParser::getSourceName(),
                ServiceTitanParser::getSourceName(),
            ], true)
        ) {
            $this->updateCustomerMetrics($message);
        }
    }

    /**
     * @param MigrationMessage $message
     * @return void
     */
    private function updateCustomerMetrics(MigrationMessage $message): void
    {
        echo
            'START Recalculating customer metrics for: '
            . $message->intacctId
            . ' - '
            . date('Y-m-d H:i:s')
            . PHP_EOL;
        $company = $this->companyRepository->findOneByIdentifier(
            $message->intacctId
        );
        if ($company) {
            $this->customerMetricsService->updateCustomerMetricsForCompany(
                $company
            );
            echo
                'DONE Recalculating customer metrics for: '
                . $message->intacctId
                . ' - '
                . date('Y-m-d H:i:s')
                . PHP_EOL;
        } else {
            echo
                'ERROR Recalculating customer metrics for: '
                . $message->intacctId
                . ' - '
                . date('Y-m-d H:i:s')
                . ': Company not found'
                . PHP_EOL;
        }
    }

    /**
     * @param string $type
     * @param MigrationMessage $message
     * @param \Exception $exception
     * @return void
     */
    private function processError(string $type, MigrationMessage $message, \Exception $exception): void
    {
        echo 'ERROR: PROCESSING: ' . $type . ': '
            . $message->intacctId
            . ': '
            . $message->downloadedFilePath
            . ': '
            . $exception->getMessage()
            . ': '
            . $exception->getTraceAsString()
            . PHP_EOL;
    }

    /**
     * @throws ReflectionException
     * @throws FileConverterException
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function processRecords(
        string $type,
        string $dataType,
        MigrationMessage $message
    ): void {
        if (
            !empty($message->dataType) &&
            $message->dataType !== $dataType
        ) {
            echo sprintf(
                "SKIPPING %s. '%s' dataType selected.",
                $type,
                $message->dataType
            ) . PHP_EOL;

            return;
        }

        try {
            echo
                $message->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                .
                'START: PROCESSING: ' . $type . ': '
                . $message->intacctId
                . ': '
                . $message->downloadedFilePath
                . PHP_EOL;
            $migrationResult = $this->migrationService->migrate(
                $message->intacctId,
                $message->downloadedFilePath,
                $message->dataSource,
                $dataType,
                $message->options,
                $message->limit,
                $message->trade
            );
            echo
                $message->intacctId
                . ': '
                . date('Y-m-d H:i:s')
                . ': '
                .
                'DONE: PROCESSING: ' . $type . ': Imported '
                . $migrationResult->getRecordCount()
                . ' records for '
                . $message->intacctId
                . ': '
                . $message->downloadedFilePath
                . PHP_EOL;
        } catch (PartialProcessingException $e) {
            $this->partialExceptions[] = $e;
            echo 'Adding Partial Processing Exception: ' . $type . ': '
                . $message->intacctId
                . ': '
                . $message->downloadedFilePath
                . ': '
                . $e->getMessage()
                . PHP_EOL;
            echo 'DONE: PROCESSING: ' . $type . ': Imported '
                . $e->getImportResult()->getRecordCount()
                . ' records for '
                . $message->intacctId
                . ': '
                . $message->downloadedFilePath
                . PHP_EOL;
        }
    }
}
