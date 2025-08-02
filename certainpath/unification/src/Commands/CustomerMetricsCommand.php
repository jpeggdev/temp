<?php

namespace App\Commands;

use App\Repository\CompanyRepository;
use App\Services\CustomerMetricsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:customer-metrics'
)]
class CustomerMetricsCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly CustomerMetricsService $customerMetricsService
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
        $this->customerMetricsService->updateCustomerMetricsForCompany(
            $company
        );
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'company',
            InputArgument::REQUIRED,
            'Company ID'
        );
    }
}
