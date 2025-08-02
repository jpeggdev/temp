<?php

namespace App\Service;

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

    public function console(string $consoleMessage): void
    {
        if (is_null($this->output)) {
            return;
        }
        $timeNow = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $this->output->writeln(
            $timeNow.': '.$consoleMessage
        );
        $this->loggerService->info(
            $consoleMessage
        );
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
