<?php

namespace App\Command;

use App\Exception\CARA\CaraAPIException;
use App\Service\CARA\TransactionSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'hub:cara:sync-transactions',
    description: 'Sync transactions with CARA',
)]
class CaraSyncTransactionsCommand extends Command
{
    public function __construct(
        private readonly TransactionSyncService $transactionSyncService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws CaraAPIException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->transactionSyncService->syncTransactions();

        $io->success('Sync transactions with CARA');

        return Command::SUCCESS;
    }
}
