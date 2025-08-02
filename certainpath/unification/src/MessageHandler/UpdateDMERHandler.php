<?php

namespace App\MessageHandler;

use App\Exceptions\DomainException\DMER\DMERProcessingException;
use App\Message\UpdateDMERMessage;
use App\Repository\CompanyRepository;
use App\Services\DMER\DMERDataService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateDMERHandler
{
    public function __construct(
        private DMERDataService $dmerDataService,
        private CompanyRepository $companyRepository,
    ) {
    }

    public function __invoke(UpdateDMERMessage $message): void
    {
        $company = $this->companyRepository->findOneBy([
            'identifier' => $message->companyIdentifier
        ]);

        if (!$company) {
            echo sprintf(
                "SKIPPING '%s'. Company not found. ",
                $message->companyIdentifier,
            ) . PHP_EOL;

            return;
        }

        try {
            echo sprintf(
                "Processing '%s'.",
                $message->companyIdentifier,
            ) . PHP_EOL;
            $this->dmerDataService->updateReportData(
                $company
            );
        } catch (DMERProcessingException | GraphException | GuzzleException $e) {
            echo sprintf(
                'Error Processing Report: %s. %s',
                $company->getIdentifier(),
                $e->getMessage(),
            );
        }
    }
}
