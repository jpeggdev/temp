<?php

namespace App\Commands;

use App\Services\CompanyDigestingAndProcessingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:process-company'
)]
class CompanyProcessingCommand extends Command
{
    public function __construct(
        private readonly CompanyDigestingAndProcessingService $companyDigestingAndProcessingService,
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->companyDigestingAndProcessingService->dispatchCompanyProcessingByIdentifier(
            $input->getArgument('company')
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
