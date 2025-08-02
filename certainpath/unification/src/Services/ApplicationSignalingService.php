<?php

namespace App\Services;

use App\Exceptions\OutputInterfaceMustBeSet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationSignalingService
{
    private ?OutputInterface $output = null;
    public function __construct(
        private readonly LoggerInterface $loggerService,
    ) {
    }
    public function debug(string $debugMessage): void
    {
        $this->loggerService->debug(
            $debugMessage
        );
    }
    public function info(string $infoMessage): void
    {
        $this->loggerService->info(
            $infoMessage
        );
    }
    public function warn(string $warnMessage): void
    {
        $this->loggerService->warning(
            $warnMessage
        );
    }
    public function error(string $errorMessage): void
    {
        $this->loggerService->error(
            $errorMessage
        );
    }
    /**
     * @throws OutputInterfaceMustBeSet
     */
    public function console(string $consoleMessage): void
    {
        if (is_null($this->output)) {
            throw new OutputInterfaceMustBeSet();
        }
        $this->output->writeln($consoleMessage);
    }
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
