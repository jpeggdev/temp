<?php

namespace App\Commands;

use App\Services\PostProcessingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use InvalidArgumentException;

#[AsCommand(
    name: 'unification:post-process',
    description: 'Apply post processing to records.',
)]
class PostProcessCommand extends Command
{
    private string $pidFile;
    public function __construct(
        private readonly PostProcessingService $postProcessingService,
        private readonly string $projectDirectory
    ) {
        $this->pidFile = sprintf(
            '%s/.%s.pid',
            $this->projectDirectory,
            (new \ReflectionClass($this))->getShortName()
        );

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('limit', InputArgument::REQUIRED, 'Specify the number of records to process.')
            ->addArgument('method', InputArgument::OPTIONAL, 'Specify which method to use')
            ->addOption('company', null, InputArgument::OPTIONAL, 'Specify the company to process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $companyIdentifier = $input->getOption('company');
        $io = new SymfonyStyle($input, $output);

        if ($this->isRunning()) {
            $io->warning('The command is already running. Exiting.');
            return Command::SUCCESS;
        }

        $this->postProcessingService->setRecordLimit($input->getArgument('limit'));

        $method = $input->getArgument('method');
        if (!$method) {
            $method = 'processRecords';
        }

        if (!method_exists($this->postProcessingService, $method)) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a valid method',
                $method
            ));
        }

        file_put_contents($this->pidFile, getmypid());
        try {
            $this->postProcessingService->$method($companyIdentifier);
            $io->success(sprintf(
                'Method %s executed',
                $method
            ));
        } finally {
            unlink($this->pidFile);
        }

        return Command::SUCCESS;
    }

    private function isRunning(): bool
    {
        if (file_exists($this->pidFile)) {
            return true;
        }

        return false;
    }
}
