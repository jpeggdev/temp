<?php

namespace App\Command;

use App\Service\AccountApplicationCompanyIngestionService;
use App\Service\StochasticCompanyIngestionService;
use Doctrine\DBAL\Exception;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'hub:company:ingest')]
class CompanyIngestionCommand extends Command
{
    public const string ACCOUNT_APPLICATION = 'account_application';
    public const string STOCHASTIC_ROSTER = 'stochastic_roster';

    public function __construct(
        private readonly AccountApplicationCompanyIngestionService $companyIngestionService,
        private readonly StochasticCompanyIngestionService $stochasticCompanyIngestionService,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws \DateMalformedStringException
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $source = $input->getOption('source');
        if (self::ACCOUNT_APPLICATION === $source) {
            $this->companyIngestionService->updateAllCompaniesFromAccountApplication();
        } elseif (self::STOCHASTIC_ROSTER === $source) {
            $this->stochasticCompanyIngestionService->updateAllCompaniesFromStochasticRoster();
        } else {
            $output->writeln('Invalid source');
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption(
            'source',
            's',
            InputOption::VALUE_REQUIRED,
            'Source of the data'
        );
    }
}
