<?php

namespace App\Commands;

use App\Services\DataProvisioner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'unification:populate-data',
    description: 'Populate some basic company and company data to get started.',
)]
class PopulateDataCommand extends Command
{
    public function __construct(
        private readonly DataProvisioner $dataProvisioner,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataProvisioner->populateWorkingData();

        $io = new SymfonyStyle($input, $output);
        $io->success('Data populated.');

        return Command::SUCCESS;
    }
}
