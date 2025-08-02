<?php

namespace App\Commands;

use App\Repository\CompanyRepository;
use App\Services\DMER\DMERFileService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:dmer:create',
    description: 'Create an empty DMER file.',
)]
class DMERCreateReportCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly DMERFileService $dmerFileService,
    ) {
        parent::__construct();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws \Throwable
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

        $shareLink = $this->dmerFileService->generateNewReport($company);
        $output->writeln($shareLink->jsonSerialize()['link']['webUrl']);

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
