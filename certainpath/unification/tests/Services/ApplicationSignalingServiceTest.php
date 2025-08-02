<?php

namespace App\Tests\Services;

use App\Exceptions\OutputInterfaceMustBeSet;
use App\Services\ApplicationSignalingService;
use App\Tests\FunctionalTestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationSignalingServiceTest extends FunctionalTestCase
{
    public function testAutoBootstrappedService(): void
    {
        $service = $this->getApplicationSignalingService();
        self::assertNotNull($service);
    }

    /**
     * @throws OutputInterfaceMustBeSet
     */
    public function testConsoleMessage(): void
    {
        $consoleMessage = 'console log';
        $outputMock = Mockery::mock(OutputInterface::class);
        $outputMock->shouldReceive('writeln')
            ->once()
            ->with(
                $consoleMessage
            );
        $service = $this->getApplicationSignalingService();
        $service->setOutput(
            $outputMock
        );
        $service->console(
            $consoleMessage
        );
        Mockery::close();
    }

    public function testSignaling(): void
    {
        $loggerService = Mockery::mock(LoggerInterface::class);

        $debugMessage = 'debug log';
        $loggerService->shouldReceive('debug')
            ->once()
            ->with(
                $debugMessage
            );
        $infoMessage = 'info log';
        $loggerService->shouldReceive('info')
            ->once()
            ->with(
                $infoMessage
            );
        $warnMessage = 'warn log';
        $loggerService->shouldReceive('warning')
            ->once()
            ->with(
                $warnMessage
            );
        $errorMessage = 'error log';
        $loggerService->shouldReceive('error')
            ->once()
            ->with(
                $errorMessage
            );

        $service = new ApplicationSignalingService(
            $loggerService
        );
        self::assertNotNull(
            $service
        );

        $service->debug(
            $debugMessage
        );
        $service->info(
            $infoMessage
        );
        $service->warn(
            $warnMessage
        );
        $service->error(
            $errorMessage
        );
        $consoleMessage = 'console log';
        $this->expectException(
            OutputInterfaceMustBeSet::class
        );
        $service->console(
            $consoleMessage
        );
        Mockery::close();
    }
}
