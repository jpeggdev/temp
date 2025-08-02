<?php

namespace App\MessageHandler\CampaignIterations;

use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Message\CampaignIterations\CreateCampaignIterationsMessage;
use App\Repository\CampaignRepository;
use App\Services\CampaignIteration\CreateCampaignIterationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateCampaignIterationsHandler
{
    public function __construct(
        private CampaignRepository $campaignRepository,
        private CreateCampaignIterationService $createCampaignIterationService,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CampaignNotFoundException
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    public function __invoke(CreateCampaignIterationsMessage $message): void
    {
        $this->createCampaignIterations($message);
    }

    /**
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CampaignNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function createCampaignIterations(CreateCampaignIterationsMessage $message): void
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($message->campaignId);

        $this->createCampaignIterationService->createCampaignIterations(
            $campaign,
            $message->createCampaignDTO
        );
    }
}
