<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Company\CompanyRosterSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'hub:sync-companies', description: 'Sync Companies Rosters')]
class SyncCompaniesRostersCommand extends Command
{
    public function __construct(
        private readonly CompanyRosterSyncService $companyRosterSyncService,
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->companyRosterSyncService->setSignaling(
            $output
        );
        $this->companyRosterSyncService->syncAllCompanies();

        return Command::SUCCESS;
    }
}
