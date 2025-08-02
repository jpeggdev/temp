<?php

namespace App\MessageHandler;

use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Message\CompanyProcessingMessage;
use App\Services\CompanyDigestingAndProcessingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class CompanyProcessingHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private CompanyDigestingAndProcessingService $companyDigestingAndProcessingService,
    ) {
    }

    /**
     * @param CompanyProcessingMessage $message
     * @throws CompanyNotFoundException
     * @throws Throwable
     */
    public function __invoke(CompanyProcessingMessage $message): void
    {
        $this->logger->info(
            'Handling company processing message',
            ['company' => $message->companyIdentifier]
        );
        $this->companyDigestingAndProcessingService->handleCompanyProcessingMessage(
            $message
        );
    }
}
