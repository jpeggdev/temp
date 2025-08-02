<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Company\CompanyRosterSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(name: 'hub:sync-company', description: 'Sync Single Company Roster')]
class SyncSingleCompanyCommand extends Command
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
        $intacctId = $input->getArgument('intacctId');
        $this->companyRosterSyncService->syncSingleCompanyByIntacctId(
            $intacctId
        );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'intacctId',
            InputArgument::REQUIRED,
            'The Intacct ID of the company to sync'
        );
    }
}
