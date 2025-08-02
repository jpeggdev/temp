<?php

namespace App\Commands;

use App\Repository\CompanyRepository;
use App\Services\DMER\DMERDataService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'unification:dmer:queue-reports',
    description: 'Find Companies (default) or specify a single Company that needs their DMER reports updated. Add to the update queue for processing.',
)]
class DMERQueueReportsCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly DMERDataService $dmerDataService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'company',
            null,
            'Company Identifier',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $companies = $this->companyRepository->getCompaniesNeedingDMERUpdate();
        if ($input->getArgument('company')) {
            $company = $this->companyRepository->findOneByIdentifier(
                $input->getArgument('company')
            );
            if (!$company) {
                $output->writeln('Company not found');
                return Command::FAILURE;
            }
            $companies = [$company];
        }

        foreach ($companies as $company) {
            $this->dmerDataService->dispatchCompanyDMERUpdate($company);
            $io->write('Dispatching ' . $company->getIdentifier() . PHP_EOL);
        }

        $io->success(sprintf(
            '%s DMER reports queued.',
            count($companies)
        ));

        return Command::SUCCESS;
    }
}
