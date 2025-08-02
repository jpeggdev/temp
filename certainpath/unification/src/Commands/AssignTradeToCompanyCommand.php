<?php

namespace App\Commands;

use App\Repository\CompanyRepository;
use App\Repository\TradeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:assign-trade-to-company',
    description: 'Assign a Trade to a Company.',
)]
class AssignTradeToCompanyCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly TradeRepository $tradeRepository,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $company = $this->companyRepository->findOneByIdentifier(
            $input->getArgument('company')
        );
        if (!$company) {
            $output->writeln('Company not found');
            return Command::FAILURE;
        }

        $trade = $this->tradeRepository->findByName(
            $input->getArgument('trade')
        );
        if (!$trade) {
            $trades = $this->tradeRepository->findAll();
            $tradeNames = array_map(function ($trade) {
                return $trade->getName();
            }, $trades);
            $output->writeln(sprintf(
                'Trade not found. Valid tradeNames are %s',
                implode(', ', $tradeNames)
            ));
            return Command::FAILURE;
        }

        $company->addTrade($trade);
        $this->companyRepository->save($company);
        $output->writeln('Trade assigned');

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'company',
            null,
            'Company Identifier'
        );

        $this->addArgument(
            'trade',
            null,
            'Trade name'
        );
    }
}
