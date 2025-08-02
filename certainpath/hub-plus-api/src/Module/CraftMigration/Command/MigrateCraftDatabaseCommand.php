<?php

declare(strict_types=1);

namespace App\Module\CraftMigration\Command;

use App\Module\CraftMigration\CraftMigrationConstants;
use App\Module\CraftMigration\Service\CraftMigrationService;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'hub:migrate_craft_database', description: 'Import the data from the craft database.')]
class MigrateCraftDatabaseCommand extends Command
{
    public function __construct(
        private readonly CraftMigrationService $craftMigrationService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Starting import of data from Craft CMS database...');
        $skipCategories = $input->getOption('skip-categories');
        $skipTags = $input->getOption('skip-tags');
        $batchSize = (int) $input->getOption('batch-size');
        $resume = $input->getOption('resume');

        if ($skipCategories) {
            $this->logger->info('Skipping categories import.');
        }
        if ($skipTags) {
            $this->logger->info('Skipping tags import.');
        }
        if ($resume) {
            $this->logger->info('Resume mode enabled - will continue from last saved progress if available.');
        }

        $this->logger->info(sprintf('Using batch size: %d', $batchSize));

        $this->craftMigrationService->importContentFromCraftDatabase(
            $skipCategories,
            $skipTags,
            $batchSize,
            $resume
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption(
            'skip-categories',
            null,
            InputOption::VALUE_NONE,
            'Skip processing the categories. (Use if recently imported)'
        );

        $this->addOption(
            'skip-tags',
            null,
            InputOption::VALUE_NONE,
            'Skip processing the tags. (Use if recently imported)'
        );

        $this->addOption(
            'batch-size',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Number of entries to process per batch (default: '.CraftMigrationConstants::DEFAULT_BATCH_SIZE.')',
            CraftMigrationConstants::DEFAULT_BATCH_SIZE
        );

        $this->addOption(
            'resume',
            'r',
            InputOption::VALUE_NONE,
            'Resume migration from last saved progress (if available)'
        );

        $this->setHelp(
            'This command allows you to import data from the Craft CMS database into the Hub. '.PHP_EOL.
            'You can skip categories and tags if they have been recently imported.'.PHP_EOL.
            'Use --batch-size to control memory usage and performance for large datasets.'.PHP_EOL.
            'Use --resume to continue from last saved progress if migration was interrupted.'.PHP_EOL
        );
    }
}
