<?php

namespace App\Commands;

use App\Exceptions\OneDriveException;
use App\Services\OneDriveService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:dmer:populate-base-directories',
    description: 'Populate base directories.',
)]
class DMERPopulateBaseDirectoriesCommand extends Command
{
    public function __construct(
        private readonly OneDriveService $oneDriveService,
    ) {
        parent::__construct();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws OneDriveException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->oneDriveService->populateBaseDirectories();

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
    }
}
