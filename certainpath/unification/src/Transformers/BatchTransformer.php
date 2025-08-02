<?php

namespace App\Transformers;

use App\Entity\Batch;
use App\Repository\BatchRepository;
use App\Resources\ProspectFilterRuleResource;
use League\Fractal\TransformerAbstract;

class BatchTransformer extends TransformerAbstract
{
    public function __construct(
        private readonly ProspectFilterRuleResource $prospectFilterRuleResource,
        private readonly BatchRepository $batchRepository
    ) {
    }

    public function transform(Batch $batch): array
    {
        $data['id'] = $batch->getId();
        $data['name'] = $batch->getName();
        $data['description'] = $batch->getDescription();
        $data['prospects_count'] = $this->includeBatchProspectsCount($batch);
        $data['batch_status'] = $this->includeBatchStatus($batch);
        $data['campaign'] = $this->includeCampaign($batch);
        $data['campaign_iteration'] = $this->includeCampaignIteration($batch);
        $data['campaign_iteration_week'] = $this->includeCampaignIterationWeek($batch);

        return $data;
    }

    public function includeBatchStatus(Batch $batch): array
    {
        $batchStatus = $batch->getBatchStatus();

        return $batchStatus
            ? (new BatchStatusTransformer())->transform($batchStatus)
            : [];
    }

    public function includeCampaign(Batch $batch): array
    {
        $campaign = $batch->getCampaign();

        return $campaign
            ? (new CampaignTransformer($this->prospectFilterRuleResource))->transform($campaign)
            : [];
    }

    public function includeCampaignIteration(Batch $batch): array
    {
        $campaignIteration = $batch->getCampaignIteration();

        return $campaignIteration
            ? (new CampaignIterationTransformer())->transform($campaignIteration)
            : [];
    }

    public function includeCampaignIterationWeek(Batch $batch): array
    {
        $campaignIterationWeek = $batch->getCampaignIterationWeek();

        return $campaignIterationWeek
            ? (new CampaignIterationWeekTransformer())->transform($campaignIterationWeek)
            : [];
    }

    public function includeBatchProspectsCount(Batch $batch): int
    {
        return $this->batchRepository->getBatchProspectsCount($batch->getId());
    }
}
