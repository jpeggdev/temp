<?php

namespace App\Commands;

use App\Exceptions\FileCouldNotBeRetrieved;
use App\Services\BulkMigrationService;
use JsonException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:data:bulk-migration'
)]
class BulkMigrationCommand extends Command
{
    public function __construct(
        private readonly BulkMigrationService $bulkMigrationService
    ) {
        parent::__construct();
    }

    /**
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws JsonException
     * @throws FileCouldNotBeRetrieved
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $companyCountLimit = (int) $input->getOption('limit');
        $companies = $input->getOption('companies');
        $companyIdentifiers = $companies ? explode(',', $companies) : null;
        $recordLimit = $input->getOption('record-limit');
        $output->writeln(
            'Starting bulk migration...'
        );
        $imports = $this->bulkMigrationService->bulkImport(
            $companyCountLimit,
            $companyIdentifiers,
            $recordLimit
        );
        $table = new Table($output);
        $table->setHeaders(
            [
                'Company ID',
                'File',
                'Target',
                'Type',
                'Options',
            ]
        );
        foreach ($imports as $import) {
            $table->addRow(
                [
                    $import->intacctId,
                    $import->downloadedFilePath,
                    json_encode(
                        $import->options,
                        JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
                    ),
                ]
            );
        }
        $table->render();
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_REQUIRED,
            'Limit of companies to migrate'
        );

        $this->addOption(
            'companies',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Company identifiers to migrate separated by comma'
        );

        $this->addOption(
            'record-limit',
            null,
            InputOption::VALUE_OPTIONAL,
            'Limit of records to migrate'
        );
    }
}
