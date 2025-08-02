<?php

namespace App\Transformers;

use App\Entity\Campaign;
use App\Resources\ProspectFilterRuleResource;
use League\Fractal\TransformerAbstract;

class CampaignTransformer extends TransformerAbstract
{
    public function __construct(
        private readonly ProspectFilterRuleResource $prospectFilterRuleResource
    ) {
    }

    public function transform(Campaign $campaign): array
    {
        $data['id'] = $campaign->getId();
        $data['company_id'] = $campaign->getCompany()?->getId();
        $data['intacct_id'] = $campaign->getCompany()?->getIdentifier();
        $data['hub_plus_product_id'] = $campaign->getHubPlusProductId();
        $data['name'] = $campaign->getName();
        $data['description'] = $campaign->getDescription();
        $data['start_date'] = $campaign->getStartDate()?->format('Y-m-d');
        $data['end_date'] = $campaign->getEndDate()?->format('Y-m-d');
        $data['mailing_iteration_weeks'] = $campaign->getMailingFrequencyWeeks();
        $data['phone_number'] = $campaign->getPhoneNumber();
        $data['campaign_status'] = $this->includeCampaignStatus($campaign);
        $data['mail_package'] = $this->includeMailPackage($campaign);
        $data['prospect_filter_rules'] = $this->includeProspectFilterRules($campaign);
        $data['batches'] = $this->includeBatches($campaign);

        return $data;
    }

    public function includeCampaignStatus(Campaign $campaign): array
    {
        $campaignStatus = $campaign->getCampaignStatus();

        return $campaignStatus
            ? (new CampaignStatusTransformer())->transform($campaignStatus)
            : [];
    }

    public function includeMailPackage(Campaign $campaign): array
    {
        $campaignStatus = $campaign->getMailPackage();

        return $campaignStatus
            ? (new MailPackageTransformer())->transform($campaignStatus)
            : [];
    }

    private function includeProspectFilterRules(Campaign $campaign): array
    {
        $prospectFilterRules = $campaign->getProspectFilterRules();

        return !$prospectFilterRules->isEmpty()
            ? $this->prospectFilterRuleResource->transformCollection($prospectFilterRules)
            : [];
    }

    public function includeBatches(Campaign $campaign): array
    {
        $batches = [];
        foreach ($campaign->getBatches() as $batch) {
            $batches[] = [
                'id' => $batch->getId(),
                'name' => $batch->getName(),
                'description' => $batch->getDescription(),
                'prospects_count' => $batch->getProspects()?->count(),
            ];
        }

        return $batches;
    }
}
