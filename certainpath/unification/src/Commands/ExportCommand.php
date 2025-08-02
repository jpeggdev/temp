<?php

namespace App\Commands;

use App\Services\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ExportCommand extends Command
{
    public const GLOBAL_ARGUMENTS = [ ];

    public const GLOBAL_OPTIONS = [
        'verbose',
    ];


    public function __construct(
        private readonly ExportService $exportService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Global Arguments and Options
            $options = [ ];
            foreach (self::GLOBAL_ARGUMENTS as $argument) {
                if ($input->getArgument($argument)) {
                    $options[$argument] = $input->getArgument($argument);
                }
            }

            foreach (self::GLOBAL_OPTIONS as $option) {
                if ($input->getOption($option)) {
                    $options[$option] = $input->getOption($option);
                }
            }

            $companyIdentifier = $input->getArgument('company');
            $savedQueryId = (int) $input->getArgument('savedQuery');
            $dataTarget = $input->getArgument('data-target');
            $dataType = $input->getArgument('data-type');

            $export = $this->exportService->export(
                $companyIdentifier,
                $savedQueryId,
                $dataTarget,
                $dataType,
                $options
            );

            if ($input->getOption('outputTableFormat')) {
                $table = new Table($output);
                $table
                    ->setHeaders([
                        'Data Source',
                        'Data Type',
                        'Record Count',
                        'Export Count',
                        'Invalid Count',
                        'Run Time',
                        'Output String'
                    ]);

                $table
                    ->setRows([
                        [
                            $dataTarget,
                            $dataType,
                            $export->getRecordCount(),
                            $export->getExportCount(),
                            $export->getInvalidCount(),
                            $export->getRunTime(),
                            $export->getOutputString(),
                        ]
                    ]);

                $table->render();
            } else {
                $output->write($export->getOutputString());
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $errorMessageFormatted = sprintf('<comment>%s</comment>', $e->getMessage());

            app_logger()->error($e->__toString());

            $output->writeln([
                null, $errorMessageFormatted, null
            ]);
        }

        return Command::FAILURE;
    }

    protected function configure(): void
    {
        $this->setName('unification:data:export');
        $this->setDescription(sprintf(
            'Export data from %s to an external data source',
            $_ENV['APP_PROPER_NAME']
        ));

        $this->addArgument(
            'company',
            InputArgument::REQUIRED,
            'Company Identifier (ex: ABC1)'
        );

        $this->addArgument(
            'data-type',
            InputArgument::REQUIRED,
            'Data Type (ex: customers)'
        );

        $this->addArgument(
            'data-target',
            InputArgument::REQUIRED,
            'Data Source (ex: vendor)'
        );

        $this->addArgument(
            'savedQuery',
            InputArgument::REQUIRED,
            'SavedQuery Id (ex: 123)'
        );

        $this->addOption(
            'outputTableFormat',
            null,
            InputOption::VALUE_REQUIRED,
            'Return the output as a formatted table.',
            true
        );
    }
}
