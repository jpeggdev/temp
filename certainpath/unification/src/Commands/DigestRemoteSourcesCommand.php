<?php

namespace App\Commands;

use App\Services\DataStreamDigestingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use InvalidArgumentException;

#[AsCommand(
    name: 'unification:digest-remote-sources',
    description: 'Digest remote sources.',
)]
class DigestRemoteSourcesCommand extends Command
{
    private string $pidFile;
    public function __construct(
        private readonly DataStreamDigestingService $databaseConsumer,
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
        $this->addOption(
            'delete-remote',
            null,
            InputOption::VALUE_NONE,
            'Delete the remote data after digesting it'
        );
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->isRunning()) {
            $io->warning('The command is already running. Exiting.');
            return Command::SUCCESS;
        }

        if ($input->getOption('delete-remote')) {
            $this->databaseConsumer->setDeleteRemote(true);
            $io->note('Deleting remote data');
        }

        file_put_contents($this->pidFile, getmypid());
        try {
            $this->databaseConsumer->syncSources();
            $io->success(sprintf(
                'Method %s executed',
                'Sync Sources'
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
