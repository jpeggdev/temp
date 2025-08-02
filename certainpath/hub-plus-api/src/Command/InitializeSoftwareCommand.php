<?php

namespace App\Command;

use App\Repository\FieldServiceSoftwareRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'hub:software:initialize')]
class InitializeSoftwareCommand extends Command
{
    public function __construct(
        private readonly FieldServiceSoftwareRepository $fieldServiceSoftwareRepository,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->fieldServiceSoftwareRepository->initializeSoftware();

        return Command::SUCCESS;
    }
}
