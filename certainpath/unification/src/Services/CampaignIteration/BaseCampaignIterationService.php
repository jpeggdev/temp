<?php

namespace App\Services\CampaignIteration;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\Entity\Campaign;
use App\Entity\CampaignIteration;
use App\Entity\CampaignIterationStatus;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Repository\CampaignIterationRepository;
use App\Repository\CampaignIterationStatusRepository;
use App\Repository\ProspectRepository;
use Doctrine\Common\Collections\ArrayCollection;

readonly class BaseCampaignIterationService
{
    public function __construct(
        protected ProspectRepository $prospectRepository,
        protected CampaignIterationRepository $campaignIterationRepository,
        protected CampaignIterationStatusRepository $campaignIterationStatusRepository,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws ProspectFilterRuleNotFoundException
     */
    protected function getProspectsForProcessing(Campaign $campaign): ArrayCollection
    {
        $prospectFilterRulesDTO = ProspectFilterRulesDTO::createFromCampaignObject($campaign);
        return $this->prospectRepository->fetchAllByProspectFilterRulesDTO($prospectFilterRulesDTO);
    }

    /**
     * @throws CampaignIterationStatusNotFoundException
     */
    public function completeCampaignIteration(CampaignIteration $iteration): void
    {
        $statusCompleted = $this->campaignIterationStatusRepository->findOneByNameOrFail(
            CampaignIterationStatus::STATUS_COMPLETED
        );

        $iteration->setCampaignIterationStatus($statusCompleted);
        $this->campaignIterationRepository->saveCampaignIteration($iteration);
    }
}
