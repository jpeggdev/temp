<?php

namespace App\Commands;

use App\Message\MigrationMessage;
use App\MessageHandler\MigrationHandler;
use App\Parsers\MailManagerLife\MailManagerLifeParser;
use App\Services\MigrationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:data:single-migration'
)]
class SingleMigrationCommand extends Command
{
    public function __construct(
        private readonly MigrationHandler $handler,
        private readonly MigrationService $migrationService
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $filePath = $input->getArgument('filePath');
        $dataSource = $input->getOption('dataSource') ?? MailManagerLifeParser::getSourceName();
        $dataType = $input->getOption('dataType');

        if (!$filePath || !(file_exists($filePath) && is_file($filePath))) {
            $output->writeln('Valid file path is required: ' . $filePath);
            return Command::FAILURE;
        }

        if (!$this->migrationService->isFileValid($filePath)) {
            $output->writeln('Formatting of file is invalid: ' . $filePath);
            return Command::FAILURE;
        }

        $this->handler->__invoke(
            new MigrationMessage(
                $input->getArgument('company'),
                $filePath,
                $dataSource,
                $dataType,
                null,
                [],
                (int) $input->getOption('limit')
            )
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'company',
            null,
            'Company ID'
        );
        $this->addArgument(
            'filePath',
            null,
            'File path'
        );
        $this->addOption(
            'dataSource',
            null,
            InputOption::VALUE_OPTIONAL,
            'Data Source'
        );
        $this->addOption(
            'dataType',
            null,
            InputOption::VALUE_OPTIONAL,
            'Data Type'
        );
        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_OPTIONAL,
            'Limit the number of records',
            0
        );
    }
}
