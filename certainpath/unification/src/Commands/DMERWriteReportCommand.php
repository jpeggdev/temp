<?php

namespace App\Commands;

use App\Exceptions\DomainException\DMER\DMERProcessingException;
use App\Repository\CompanyRepository;
use App\Services\DMER\DMERDataService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'unification:dmer:write',
    description: 'Write a DMER report for a Company.',
)]
class DMERWriteReportCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly DMERDataService $dmerDataService,
    ) {
        parent::__construct();
    }

    /**
     * @throws Throwable
     */
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
        try {
            echo sprintf(
                "Processing '%s'.",
                $company->getIdentifier(),
            ) . PHP_EOL;
            $this->dmerDataService->updateReportData(
                $company
            );
        } catch (DMERProcessingException | GraphException | GuzzleException $e) {
            echo sprintf(
                'Error Processing Report: %s. %s',
                $company->getIdentifier(),
                $e->getMessage(),
            );
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'company',
            null,
            'Company Identifier'
        );
    }
}
