<?php

namespace App\MessageHandler\CampaignIterations;

use App\Exceptions\DomainException\Campaign\CampaignResumeFailedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignEventNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Message\CampaignIterations\ResumeCampaignIterationsMessage;
use App\Repository\CampaignEventRepository;
use App\Repository\CampaignRepository;
use App\Services\CampaignIteration\ResumeCampaignIterationService;
use Doctrine\DBAL\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ResumeCampaignIterationsHandler
{
    public function __construct(
        private CampaignRepository $campaignRepository,
        private CampaignEventRepository $campaignEventRepository,
        private ResumeCampaignIterationService $resumeCampaignIterationService,
    ) {
    }

    /**
     * @throws Exception
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CampaignNotFoundException
     * @throws MailPackageNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignResumeFailedException
     * @throws CampaignEventNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    public function __invoke(ResumeCampaignIterationsMessage $message): void
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($message->campaignId);
        $campaignEventPaused = $this->campaignEventRepository->findOneByIdOrFail($message->campaignEventPausedId);

        $this->resumeCampaignIterationService->resumeCampaignIterations(
            $campaign,
            $campaignEventPaused
        );
    }
}
